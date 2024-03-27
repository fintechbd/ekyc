<?php

namespace Fintech\Ekyc\Vendors;

use Fintech\Core\Enums\Ekyc\KycStatus;
use Fintech\Ekyc\Abstracts\KycVendor as AbstractsKycVendor;
use Fintech\Ekyc\Enums\KycAction;
use Fintech\Ekyc\Interfaces\KycVendor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class Signzy extends AbstractsKycVendor implements KycVendor
{
    public $action;

    public function __construct()
    {
        $mode = config('fintech.ekyc.providers.shufti_pro.mode', 'sandbox');

        $this->config = config("fintech.ekyc.providers.shufti_pro.{$mode}", [
            'endpoint' => 'https://api.shuftipro.com',
            'username' => null,
            'password' => null,
        ]);

        $this->payload = config('fintech.ekyc.providers.shufti_pro.options');
    }

    /**
     * @return void
     */
    public function status(string $reference)
    {
        $this->action = KycAction::StatusCheck;

        $this->call('/status', [
            'reference' => $reference,
        ]);
    }

    /**
     * @return void
     */
    public function verify()
    {
        $this->action = KycAction::Verification;

        $this->call('/');
    }

    /**
     * @return $this
     */
    public function address(string $reference, array $data = []): self
    {
        $this->type = 'address';

        return $this;
    }

    /**
     * @return $this
     */
    public function identity(string $reference, array $data = []): self
    {
        $this->type = 'identity';

        $idType = \Fintech\MetaData\Facades\MetaData::idDocType()->find($data['id_doc_type_id']);

        if (! $idType) {
            throw (new ModelNotFoundException())->setModel(config('fintech.auth.id_doc_type_model', \Fintech\MetaData\Models\IdDocType::class), $data['id_doc_type_id']);
        }

        $idType->load('country');

        $this->payload['country'] = strtoupper($idType->country->iso2);
        $this->payload['reference'] = $reference;
        $this->payload['callback_url'] = route('ekyc.kyc.status-change-callback');
        $this->payload['email'] = $data['email'] ?? '';

        $document['supported_types'] = Arr::wrap($idType->id_doc_type_data['shuftipro_document_type'] ?? 'any');
        $document['proof'] = $data['documents'][0]['front'] ?? '';
        $document['additional_proof'] = $data['documents'][1]['back'] ?? '';
        $document['backside_proof_required'] = ($idType->sides == 1) ? '0' : '1';
        $document['allow_ekyc'] = '0';
        $document['verification_instructions'] = [
            'allow_paper_based' => '1',
            'allow_photocopy' => '1',
            'allow_laminated' => '1',
            'allow_screenshot' => '1',
            'allow_cropped' => '1',
            'allow_scanned' => '1',
        ];
        $document['verification_mode'] = 'image_only';
        $document['fetch_enhanced_data'] = '1';
        $document['name'] = [
            'full_name' => $data['name'] ?? '',
            'fuzzy_match' => '1',
        ];
        $document['dob'] = $data['date_of_birth'] ?? '';
        $document['issue_date'] = $data['id_issue_at'] ?? '';
        $document['expiry_date'] = $data['id_expired_at'] ?? '';
        $document['document_number'] = $data['id_no'] ?? '';
        $document['gender'] = ($data['gender']) ? substr(strtoupper($data['gender']), 0, 1) : 'M';
        $document['age'] = [
            'min' => '18',
            'max' => '65',
        ];

        if (isset($data['photo'])) {
            $face['proof'] = $data['photo'] ?? '';
            $face['check_duplicate_request'] = '0';
            $this->payload['face'] = $face;
        }

        $this->payload['document'] = $document;

        return $this;
    }

    /**
     * @return void
     */
    private function call(string $url = '/')
    {
        if (! $this->config['username'] || ! $this->config['password']) {
            throw new \InvalidArgumentException('Shufti Pro Client ID & Secret Key is missing.');
        }

        $response = Http::withoutVerifying()->timeout(120)
            ->withBasicAuth($this->config['username'], $this->config['password'])
            ->baseUrl($this->config['endpoint'])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($url, $this->payload);

        $this->response = $response->json();

        logger('Shufti Pro Response', [$response->status(), $response->json()]);

        $this->validateResponse($response);
    }

    private function validateResponse(\Illuminate\Http\Client\Response $response): void
    {
        $this->status = KycStatus::Pending->value;

        $this->note = match ($response->status()) {
            200 => $this->eventStatusHandler($response->json()),
            400 => 'Bad Request: one or more parameter is invalid or missing',
            401 => 'Unauthorized: invalid signature key provided in the request',
            402 => 'Request Failed: invalid request data: missing required parameters',
            403 => 'Forbidden: service not allowed',
            404 => 'Not Found: Resource not found',
            409 => 'Conflict: Conflicting data: already exists',
            429 => 'Too Many Attempts.',
            500 => 'Internal Server Error',
            504 => 'Server error',
            524 => 'Timeout from Cloudflare',
        };
    }

    private function eventStatusHandler(array $response): string
    {
        $event = $response['event'];

        $this->status = match ($event) {
            'request.deleted' => KycStatus::Cancelled->value,
            'verification.declined' => KycStatus::Declined->value,
            'verification.accepted' => KycStatus::Accepted->value,
            'request.invalid' => KycStatus::Cancelled->value,
            default => KycStatus::Pending->value,
        };

        return match ($event) {
            'request.deleted' => 'Request has been deleted.',
            'verification.declined' => $response['declined_reason'] ?? 'Request was valid and declined after verification.',
            'verification.accepted' => 'Document KYC Verification Completed.',
            'request.invalid' => $response['error']['message'] ?? 'The given data is invalid',
            default => 'Documents are collected and request is pending for admin to review and Accept/Decline. Reference No: #'.$response['reference'],
        };
    }
}

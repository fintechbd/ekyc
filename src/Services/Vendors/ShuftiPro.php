<?php

namespace Fintech\Ekyc\Services\Vendors;

use Fintech\Core\Enums\Ekyc\KycStatus;
use Fintech\Ekyc\Abstracts\KycVendor as AbstractsKycVendor;
use Fintech\Ekyc\Interfaces\KycVendor;
use Fintech\MetaData\Facades\MetaData;
use Fintech\MetaData\Models\Catalog;
use Fintech\MetaData\Models\Country;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class ShuftiPro extends AbstractsKycVendor implements KycVendor
{
    public function __construct()
    {
        $this->mode = config('fintech.ekyc.providers.shufti_pro.mode', 'sandbox');

        $this->config = config("fintech.ekyc.providers.shufti_pro.{$this->mode}", [
            'endpoint' => 'https://api.shuftipro.com',
            'client_id' => null,
            'secret_key' => null,
        ]);

        $this->payload = config('fintech.ekyc.providers.shufti_pro.options');
    }

    /**
     * @return void
     */
    public function status(string $reference)
    {
        $this->call('/status', [
            'reference' => $reference,
        ]);
    }

    /**
     * @return void
     */
    private function call(string $url = '/')
    {
        if (!$this->config['client_id'] || !$this->config['secret_key']) {
            throw new InvalidArgumentException('Shufti Pro Client ID & Secret Key is missing.');
        }

        $response = Http::withoutVerifying()->timeout(120)
            ->withBasicAuth($this->config['client_id'], $this->config['secret_key'])
            ->baseUrl($this->config['endpoint'])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($url, $this->payload);

        $this->response = $response->json();

        $this->validateResponse($response);
    }

    private function validateResponse(Response $response): void
    {
        $this->status = KycStatus::Pending->value;

        $this->note = match ($response->status()) {
            200, 400 => $this->eventStatusHandler($response->json()),//'Bad Request: one or more parameter is invalid or missing',
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
            default => 'Documents are collected and request is pending for admin to review and Accept/Decline. Reference No: #' . $response['reference'],
        };
    }

    public function verify(string $reference, array $data = []): void
    {
        $idType = MetaData::idDocType()->find($data['id_doc_type_id']);

        if (!$idType) {
            throw (new ModelNotFoundException)->setModel(config('fintech.metadata.catalog_model', Catalog::class), $data['id_doc_type_id']);
        }

        $country = $idType->countries->firstWhere('name', $data['id_issue_country']);

        if (!$country) {
            throw (new ModelNotFoundException)->setModel(config('fintech.metadata.country_model', Country::class), $data['id_issue_country']);
        }

        $this->payload['country'] = strtoupper($country->iso2);
        $this->payload['reference'] = $reference;
        $this->payload['callback_url'] = route('ekyc.kyc.status-change-callback');
        $this->payload['email'] = $data['email'] ?? '';
        $document['supported_types'] = Arr::wrap($idType->vendor_code['ekyc']['shufti_pro'] ?? 'any');
        $document['proof'] = $data['documents'][0]['front'] ?? '';
        if (!empty($data['documents'][1]['back'])) {
            $document['additional_proof'] = $data['documents'][1]['back'];
        }
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
        //        $document['name'] = [
        //            'full_name' => $data['name'] ?? '',
        //            'fuzzy_match' => '1',
        //        ];
        //        $document['dob'] = $data['date_of_birth'] ?? '';
        //        $document['issue_date'] = $data['id_issue_at'] ?? '';
        //        $document['expiry_date'] = $data['id_expired_at'] ?? '';
        //        $document['document_number'] = $data['id_no'] ?? '';
        //        $document['gender'] = ($data['gender']) ? substr(strtoupper($data['gender']), 0, 1) : 'M';
        //        $document['age'] = [
        //            'min' => '18',
        //            'max' => '65',
        //        ];

        if (!empty($data['photo'])) {
            $face['proof'] = $data['photo'] ?? '';
            $face['check_duplicate_request'] = '0';
            $this->payload['face'] = $face;
        }
        //
        //        if (! empty($data['proof_of_address'])) {
        //
        //            $city = \Fintech\MetaData\Facades\MetaData::city()->find($data['present_city_id']);
        //
        //            $state = \Fintech\MetaData\Facades\MetaData::state()->find($data['present_state_id']);
        //
        //            $country = \Fintech\MetaData\Facades\MetaData::country()->find($data['present_country_id']);
        //
        //            $full_address = $data['present_address'];
        //
        //            if ($city) {
        //                $full_address .= ", {$city->name}";
        //            }
        //
        //            if ($state) {
        //                $full_address .= ", {$state->name}";
        //            }
        //
        //            if (! empty($data['present_post_code'])) {
        //                $full_address .= ", {$data['present_post_code']}";
        //            }
        //
        //            if ($country) {
        //                $full_address .= ", {$country->name}.";
        //            }
        //
        //            $address['proof'] = $data['proof_of_address'][''] ?? '';
        //            $address['supported_types'] = ['any'];
        //            $address['full_address'] = $full_address;
        //            $address['address_fuzzy_match'] = '1';
        //            $address['backside_proof_required'] = '0';
        //            $address['verification_mode'] = 'any';
        //            $this->payload['address'] = $address;
        //        }

        $this->payload['document'] = $document;

        $this->call('/');
    }

    /**
     * update the current credentials
     */
    public function syncCredential(): bool
    {
        // TODO: Implement syncCredential() method.
    }
}

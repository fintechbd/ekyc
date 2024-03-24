<?php

namespace Fintech\Ekyc\Vendors;

use Fintech\Core\Facades\Core;
use Fintech\Ekyc\Enums\KycAction;
use Fintech\Ekyc\Interfaces\KycVendor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ShuftiPro implements KycVendor
{
    public $config;

    public $action;

    private $userModel;

    private $profileModel;

    private $kycStatusModel;

    private string $reference;

    private array $payload;

    private string $type;

    private $response;

    public function __construct()
    {
        $mode = config('fintech.ekyc.providers.shufti_pro.mode', 'sandbox');

        $this->config = config("fintech.ekyc.providers.shufti_pro.{$mode}", [
            'endpoint' => 'https://api.shuftipro.com',
            'username' => null,
            'password' => null,
        ]);

        $this->payload = $this->config['options'];
    }

    /**
     * @return void
     */
    private function call(string $url = '/')
    {
        if (! $this->config['username'] || ! $this->config['password']) {
            throw new \InvalidArgumentException('Shufti Pro Client ID & Secret Key is missing.');
        }

        $response = Http::withoutVerifying()
            ->withBasicAuth($this->config['username'], $this->config['password'])
            ->baseUrl($this->config['endpoint'])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($url, $this->payload);

        logger('Shufti Pro', [$response->body()]);

        $responseBody = $this->httpErrorHandler($response);
    }

    private function httpErrorHandler(\Illuminate\Http\Client\Response $response)
    {
        return match ($response->status()) {
            200 => $response->json(),
            400 => ['message' => 'Bad Request: one or more parameter is invalid or missing'],
            401 => ['message' => 'Unauthorized: invalid signature key provided in the request'],
            402 => ['message' => 'Request Failed: invalid request data: missing required parameters'],
            403 => ['message' => 'Forbidden: service not allowed'],
            404 => ['message' => 'Not Found: Resource not found'],
            409 => ['message' => 'Conflict: Conflicting data: already exists'],
            429 => ['message' => 'Too Many Attempts.'],
            500 => ['message' => 'Internal Server Error'],
            504 => ['message' => 'Server error'],
            524 => ['message' => 'Timeout from Cloudflare'],
        };
    }

    private function eventStatusHandler(array $response)
    {

    }

    /**
     * load the user that will go to kyc verification
     */
    public function user(string|int $id): self
    {
        if (! Core::packageExists('Auth')) {
            throw new \InvalidArgumentException('`Auth` package is missing from the system.');
        }

        $user = \Fintech\Auth\Facades\Auth::user()->find($id);

        if (! $user) {
            throw (new ModelNotFoundException())->setModel(config('fintech.auth.user_model', \Fintech\Auth\Models\User::class), $id);
        }

        $user->load('profile');

        $this->payload['email'] = $user->email ?? '';

        $this->userModel = $user;

        $this->profileModel = $user->profile;

        return $this;
    }

    public function status(string $reference)
    {
        $this->action = KycAction::StatusCheck;

        $this->call('/status', [
            'reference' => $reference,
        ]);
    }

    public function verify()
    {
        $this->action = KycAction::Verification;

        $this->call('/');
    }

    private function userModelConfiguredCheck(): void
    {
        $class = config('fintech.auth.user_model', \Fintech\Auth\Models\User::class);

        if ($this->userModel == null || $this->userModel instanceof $class) {
            throw new \InvalidArgumentException('Before setting verification use the `user()` method call.');
        }
    }

    public function address(): self
    {
        $this->userModelConfiguredCheck();

        $this->type = 'address';

        return $this;
    }

    public function identity(array $data = []): self
    {
        $this->type = 'identity';

        $idType = \Fintech\Auth\Facades\Auth::idDocType()->find($data['id_doc_type_id']);

        if (! $idType) {
            throw (new ModelNotFoundException())->setModel(config('fintech.auth.id_doc_type_model', \Fintech\Auth\Models\IdDocType::class), $data['id_doc_type_id']);
        }

        $idType->load('country');

        $this->payload['country'] = strtoupper($idType->country->iso2);

        $document['supported_types'] = Arr::wrap($idType->id_doc_type_data['shuftipro_document_type'] ?? 'any');
        $document['backside_proof_required'] = (string) ($idType->sides ?? '0');
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

        $this->payload['document'] = $document;

        return $this;
    }

    public function delete(string $reference, array $options = [])
    {
        $this->action = KycAction::Cancellation;

        $this->call('/delete', [
            'reference' => $reference,
            'comment' => $options['note'] ?? 'Invalid or updated document will be provided later.',
        ]);
    }

    public function reference(?string $reference = null): string|self
    {
        if ($reference != null) {
            $this->reference = $reference;

            return $this;
        }

        return $this->reference;
    }

    public function getPayload(): mixed
    {
        return $this->payload;
    }

    public function getResponse(): mixed
    {
        return $this->response;
    }
}

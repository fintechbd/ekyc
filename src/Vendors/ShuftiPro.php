<?php

namespace Fintech\Ekyc\Vendors;

use Fintech\Core\Facades\Core;
use Fintech\Ekyc\Interfaces\KycVendor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Http;

class ShuftiPro implements KycVendor
{
    public $config;

    public $mode;
    private $userModel;
    private $profileModel;
    private array $payload;
    private string $type;

    public function __construct()
    {
        $this->mode = config('fintech.ekyc.providers.shufti_pro.mode', 'sandbox');

        $this->config = config("fintech.ekyc.providers.shufti_pro.{$this->mode}", [
            'endpoint' => 'https://api.shuftipro.com',
            'username' => null,
            'password' => null,
        ]);

        $this->payload = [
            'reference' => '',
            'country' => '', //id issue county
            'language' => config('app.locale'),
            'email' => '',
            'verification_mode' => 'any',
            'allow_offline' => '1',
            'allow_online' => '0',
            'allow_retry' => '1',
            'show_consent' => '0',
            'decline_on_single_step' => '1',
            'enhanced_originality_checks' => '1',
            'manual_review' => '0',
        ];

        $this->userModel = null;
    }

    /**
     * @return void
     */
    private function call(string $url = '/')
    {
        if (!$this->config['username'] || !$this->config['password']) {
            throw new \InvalidArgumentException('Shufti Pro Client ID & Secret Key is missing.');
        }

        $response = Http::withoutVerifying()
            ->withBasicAuth($this->config['username'], $this->config['password'])
            ->baseUrl($this->config['endpoint'])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($url, $this->payload);

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
     *
     * @param string|int $id
     * @return self
     */
    public function user(string|int $id): self
    {
        if (!Core::packageExists('Auth')) {
            throw new \InvalidArgumentException("`Auth` package is missing from the system.");
        }

        $user = \Fintech\Auth\Facades\Auth::user()->find($id);

        if (!$user) {
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
        $this->call('/status', [
            'reference' => $reference,
        ]);
    }

    public function verify()
    {

    }

    private function userModelConfiguredCheck(): void
    {
        $class = config('fintech.auth.user_model', \Fintech\Auth\Models\User::class);

        if ($this->userModel == null || $this->userModel instanceof $class) {
            throw new \InvalidArgumentException("Before setting verification use the `user()` method call.");
        }
    }

    public function address(): self
    {
        $this->userModelConfiguredCheck();

        $this->type = 'address';


        return $this;
    }

    public function identity(): self
    {
        $this->userModelConfiguredCheck();

        $this->type = 'identity';

        $document['supported_types'] = [$this->profileModel->id_type ?? 'passport'];
        $document['backside_proof_required'] = '0';
        $document['allow_ekyc'] = '0';
        $document['verification_instructions'] = [
            'allow_paper_based' => '1',
            'allow_photocopy' => '1',
            'allow_laminated' => '1',
            'allow_screenshot' => '1',
            'allow_cropped' => '1',
            'allow_scanned' => '1'
        ];
        $document['verification_mode'] = 'any';
        $document['fetch_enhanced_data'] = '1';
        $document['name'] = [
            'full_name' => $this->userModel->name ?? '',
            'fuzzy_match' => "1"
        ];
        $document['dob'] = $this->profileModel->date_of_birth ?? '';
        $document['issue_date'] = $this->profileModel->id_expired_at ?? '';
        $document['expiry_date'] = $this->profileModel->id_expired_at ?? '';
        $document['document_number'] = $this->profileModel->id_no ?? '';
        $document['gender'] = ($this->profileModel->user_profile_data['gender']) ? substr(strtoupper($this->profileModel->user_profile_data['gender']), 0, 1) : 'M';
        $document['age'] = [
            'min' => '18',
            'max' => '65'
        ];

        $this->payload['document'] = $document;

        return $this;
    }

    public function delete(string $reference, array $options = [])
    {
        $this->call('/delete', [
            'reference' => $reference,
            'comment' => $options['note'] ?? 'Invalid or updated document will be provided later.',
        ]);
    }
}

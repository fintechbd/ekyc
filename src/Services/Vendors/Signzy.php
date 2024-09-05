<?php

namespace Fintech\Ekyc\Services\Vendors;

use Carbon\CarbonImmutable;
use Fintech\Core\Enums\Ekyc\KycStatus;
use Fintech\Core\Facades\Core;
use Fintech\Core\Supports\Base64File;
use Fintech\Ekyc\Abstracts\KycVendor as AbstractsKycVendor;
use Fintech\Ekyc\Interfaces\KycVendor;
use Fintech\MetaData\Facades\MetaData;
use Fintech\MetaData\Models\Catalog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidBase64Data;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;

class Signzy extends AbstractsKycVendor implements KycVendor
{
    private ?string $accessToken;

    private ?string $patronId;

    private mixed $options;

    public function __construct()
    {
        $this->mode = config('fintech.ekyc.providers.signzy.mode', 'sandbox');

        $this->config = config("fintech.ekyc.providers.signzy.{$this->mode}", [
            'endpoint' => 'https://preproduction.signzy.tech/api/v2/patrons',
            'username' => null,
            'password' => null,
        ]);

        $this->options = config('fintech.ekyc.providers.signzy.options');

        $this->accessToken = $this->options['access_token'];

        $this->patronId = $this->options['patron_id'];
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

    private function call(string $url = '/'): void
    {
        if (!$this->config['username'] || !$this->config['password']) {
            throw new InvalidArgumentException('Signzy Username or Password is missing.');
        }

        //pre-request token verification
        $this->connectionCheck();

        $response = Http::withoutVerifying()
            ->timeout(120)
            ->baseUrl($this->config['endpoint'])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => $this->accessToken,
            ])
            ->post($url, $this->payload);

        $this->response = $response->json();

        $this->validateResponse($response);
    }

    public function connectionCheck(): void
    {
        if ($this->options['expired_at'] != null) {
            $expiration = CarbonImmutable::parse($this->options['expired_at']);
            if ($expiration->gt(CarbonImmutable::now()->addDays())) {
                return;
            }
        }

        $response = Http::withoutVerifying()
            ->timeout(30)
            ->baseUrl($this->config['endpoint'])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post('/login', [
                'username' => $this->config['username'],
                'password' => $this->config['password'],
            ])->json();

        $this->accessToken = $response['id'];
        $this->patronId = $response['userId'];
        $expirationDate = CarbonImmutable::now()->addSeconds($response['ttl']);

        Core::setting()->setValue('ekyc', 'providers.signzy.options', [
            'expired_at' => $expirationDate->format('Y-m-d H:i:s'),
            'access_token' => $this->accessToken,
            'patron_id' => $this->patronId,
        ], 'array');
    }

    private function validateResponse(Response $response): void
    {
        $this->status = ($response->status() == 200) ? KycStatus::Accepted->value : KycStatus::Pending->value;

        $this->note = match ($response->status()) {
            200 => 'Document KYC Verification Completed.',
            400 => $response->json()['error']['message'] ?? 'The given data is invalid',
            401 => 'Unauthorized: invalid signature key provided in the request',
            402 => 'Request Failed: invalid request data: missing required parameters',
            403 => 'Forbidden: service not allowed',
            404 => 'Not Found: Resource not found',
            409 => 'Conflict: Conflicting data: already exists',
            422 => 'Missing Inputs or the JSON body of a request is badly-formed.',
            429 => 'Too Many Attempts.',
            500 => 'Internal Server Error',
            504 => 'Server error',
            524 => 'Timeout from Cloudflare',
        };
    }

    /**
     * @throws InvalidBase64Data
     * @throws MimeTypeNotAllowed
     */
    public function verify(string $reference, array $data = []): void
    {
        $idType = MetaData::idDocType()->find($data['id_doc_type_id']);

        if (!$idType) {
            throw (new ModelNotFoundException)->setModel(config('fintech.metadata.catalog_model', Catalog::class), $data['id_doc_type_id']);
        }

        $this->payload['task'] = 'idIntelligence';

        $this->payload['essentials'] = [];

        if (isset($data['documents'][0]['front'])) {

            $frontFilePath = Base64File::load($data['documents'][0]['front'],
                ['image/jpg', 'image/jpeg', 'image/png'])
                ->save('front', $reference);

            $frontFileUrl = Storage::disk(config('filesystems.default', 'public'))->url($frontFilePath);

            $this->payload['essentials']['frontUrl'] = $frontFileUrl;

            $this->payload['essentials']['images'][0] = $frontFileUrl;
        }

        if (isset($data['documents'][1]['back']) && $idType->sides != 1) {

            $backFilePath = Base64File::load($data['documents'][1]['back'],
                ['image/jpg', 'image/jpeg', 'image/png'])
                ->save('back', $reference);

            $backFileUrl = Storage::disk(config('filesystems.default', 'public'))->url($backFilePath);

            $this->payload['essentials']['backUrl'] = $backFileUrl;

            $this->payload['essentials']['images'][1] = $backFileUrl;
        }

        $this->payload['essentials']['country'] = $data['id_issue_country'];
        $this->payload['essentials']['idType'] = $idType->vendor_code['ekyc']['signzy'] ?? 'Other Id Card';
        $this->payload['essentials']['performImageQualityAnalysis'] = false;
        $this->payload['essentials']['performIdClassification'] = true;
        $this->payload['essentials']['performIdExtraction'] = true;
        $this->payload['essentials']['performFaceExtraction'] = false;
        $this->payload['essentials']['imageQualityThreshold'] = 0.5;

        $this->call("/{$this->patronId}/documentIntelligence");
    }

    /**
     * update the current credentials
     */
    public function syncCredential(): bool
    {
        return false;
    }

    private function logout()
    {
        Http::withoutVerifying()->timeout(30)
            ->baseUrl($this->config['endpoint'])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post('/logout', ['access_token' => $this->accessToken]);

    }
}

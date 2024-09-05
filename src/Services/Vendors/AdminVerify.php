<?php

namespace Fintech\Ekyc\Services\Vendors;

use Fintech\Ekyc\Abstracts\KycVendor as AbstractsKycVendor;
use Fintech\Ekyc\Interfaces\KycVendor;
use Illuminate\Support\Facades\Http;

class AdminVerify extends AbstractsKycVendor implements KycVendor
{
    public $config;

    public function __construct()
    {
        $this->mode = config('fintech.ekyc.providers.shufti_pro.mode', 'sandbox');

        $this->config = config("fintech.ekyc.providers.shufti_pro.{$this->mode}", [
            'endpoint' => 'https://api.shuftipro.com',
            'username' => '',
            'password' => '',
        ]);

    }

    public function status(array $reference = [])
    {
    }

    public function verify(string $reference, array $data = []): void
    {
    }

    public function delete(array $reference = [])
    {
    }

    /**
     * update the current credentials
     */
    public function syncCredential(): bool
    {
        // TODO: Implement syncCredential() method.
    }

    private function call($data = [])
    {
        $response = Http::withoutVerifying()
            ->withBasicAuth($this->config['username'], $this->config['password'])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($this->config['endpoint'], $data);
    }
}

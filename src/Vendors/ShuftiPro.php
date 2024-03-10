<?php

namespace Fintech\Ekyc\Vendors;

use Fintech\Ekyc\Interfaces\KycVendor;
use Illuminate\Support\Facades\Http;

class ShuftiPro implements KycVendor
{
    public $config;

    public $mode;

    public function __construct()
    {
        $this->mode = config('fintech.ekyc.providers.shufti_pro.mode', 'sandbox');

        $this->config = config("fintech.ekyc.providers.shufti_pro.{$this->mode}", [
            'endpoint' => 'https://api.shuftipro.com',
            'username' => null,
            'password' => null,
        ]);

    }

    private function call($data = [])
    {
        if (!$this->config['username'] || !$this->config['password']) {
            throw new \InvalidArgumentException("Shufti Pro Client ID & Secret Key is missing.");
        }

        $response = Http::withoutVerifying()
            ->withBasicAuth($this->config['username'], $this->config['password'])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->config['endpoint'], $data);
    }

    public function status(array $reference = [])
    {

    }

    public function verify(array $data = [])
    {

    }

    public function delete(array $reference = [])
    {

    }
}

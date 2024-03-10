<?php

namespace Fintech\Ekyc\Vendors;

use Fintech\Ekyc\Interfaces\KycVendor;
use Illuminate\Support\Facades\Http;

class AdminVerify implements KycVendor
{
    public $config;

    public $mode;

    public function __construct()
    {
        $this->mode = config('fintech.ekyc.providers.shufti_pro.mode', 'sandbox');

        $this->config = config("fintech.ekyc.providers.shufti_pro.{$this->mode}", [
            'endpoint' => 'https://api.shuftipro.com',
            'username' => '',
            'password' => '',
        ]);

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

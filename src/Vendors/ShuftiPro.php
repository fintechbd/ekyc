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
            524 => ['message' => 'Timeout from Cloudflare	'],
        };
    }

    private function eventStatusHandler(array $response)
    {

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

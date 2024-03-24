<?php

namespace Fintech\Ekyc\Abstracts;

class KycVendor
{
    protected $config;

    protected $payload;

    protected $response;

    protected string $note;

    protected string $status;

    public function getPayload(): mixed
    {
        return $this->payload;
    }

    public function getResponse(): mixed
    {
        return $this->response;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getNote(): string
    {
        return $this->note;
    }
}

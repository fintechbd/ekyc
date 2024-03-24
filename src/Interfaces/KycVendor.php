<?php

namespace Fintech\Ekyc\Interfaces;

interface KycVendor
{
    /**
     * check a review status of a request document
     * on kyc vendor site
     *
     * @return mixed
     */
    public function status(string $reference);

    /**
     * Make a request with all document to verify on kyc system
     *
     * @return mixed
     */
    public function verify();

    /**
     * load the user that will go to kyc verification
     */
    public function identity(string $reference, array $data = []): self;

    /**
     * load the user that will go to kyc verification
     */
    public function address(string $reference, array $data = []): self;

    public function getPayload(): mixed;

    public function getResponse(): mixed;

    public function getStatus(): string;

    public function getNote(): string;
}

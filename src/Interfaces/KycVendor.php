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
     * make a request o kyc partner to erase verification document
     * previously provided
     *
     * @return mixed
     */
    public function delete(string $reference, array $options = []);

    /**
     * load the user that will go to kyc verification
     */
    public function user(string|int $id): self;

    public function identity(array $data = []): self;
    public function reference(string $reference = null): string|self;
    public function getPayload(): mixed;
    public function getResponse(): mixed;
}

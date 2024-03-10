<?php

namespace Fintech\Ekyc\Interfaces;

interface KycVendor
{
    /**
     * check a review status of a request document
     * on kyc vendor site
     * @param array $reference
     * @return mixed
     */
    public function status(array $reference = []);

    /**
     * Make a request with all document to verify on kyc system
     *
     * @param array $data
     * @return mixed
     */
    public function verify(array $data = []);

    /**
     * make a request o kyc partner to erase verification document
     * previously provided
     *
     * @param array $reference
     * @return mixed
     */
    public function delete(array $reference = []);
}

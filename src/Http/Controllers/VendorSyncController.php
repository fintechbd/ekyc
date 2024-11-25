<?php

namespace Fintech\Ekyc\Http\Controllers;

use ErrorException;
use Exception;
use Fintech\Ekyc\Interfaces\KycVendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class VendorSyncController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(?string $vendor = null): JsonResponse
    {
        try {

            $driver = config("fintech.ekyc.providers.{$vendor}.driver");

            if (! $driver) {
                throw new ErrorException("Missing driver for `{$vendor}` kyc provider.");
            }
            /**
             * @var KycVendor $instance
             */
            $instance = app()->make($driver);

            $response = $instance->syncCredential();

        } catch (Exception $exception) {

            return response()->failed($exception);
        }
    }

    private function shuftiPro() {}

    private function signzy() {}
}

<?php

namespace Fintech\Ekyc\Http\Controllers;

use Exception;
use Fintech\Ekyc\Http\Requests\KycVerificationRequest;
use Fintech\Ekyc\Http\Resources\KycVerificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class KycHandlerController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function verification(KycVerificationRequest $request, ?string $vendor = null): JsonResponse|KycVerificationResource
    {
        try {

            if ($vendor == null) {
                $vendor = config('fintech.ekyc.default', 'manual');
            }

            $inputs = $request->validated();

            $kycStatus = ekyc()->kycStatus()->verify($vendor, $inputs);

            return new KycVerificationResource($kycStatus);

        } catch (Exception $exception) {

            return response()->failed($exception);
        }
    }

    /**
     * @lrd:start
     * this return any enabled kyc vendor login credentials and options.
     * current available vendors list is avaliable in `/api/ekyc/kyv-vendors`
     *
     * @lrd:end
     */
    public function credential(?string $vendor = null): JsonResponse
    {
        if ($vendor == null) {
            $vendor = config('fintech.ekyc.default');
        }

        $config = config("fintech.ekyc.providers.{$vendor}");

        $mode = $config['mode'] ?? 'sandbox';

        $credentials = $config[$mode] ?? [];

        return response()->success([
            'data' => [
                'credentials' => $credentials,
                'options' => $config['options'] ?? [],
            ],
            'query' => [
                'vendor' => $vendor,
            ],
        ]);

    }

    /**
     * @lrd:start
     * this return current kyc vendors login credentials.
     *
     * @lrd:end
     */
    public function vendor(): JsonResponse
    {

        $providers = config('fintech.ekyc.providers');

        $vendors = array_keys($providers);

        $data = [];

        foreach ($vendors as $vendor) {
            $data[] = [
                'vendor' => $vendor,
                'countries' => $providers[$vendor]['countries'] ?? [],
            ];
        }

        return response()->success(['data' => $data]);

    }

    /**
     * @lrd:start
     * this return kyc vendor reference token and
     * increment the internal count by one.
     *
     * @lrd:end
     */
    public function token(): JsonResponse
    {
        return response()->success(['data' => ['reference_no' => ekyc()->getReferenceToken()]]);
    }
}

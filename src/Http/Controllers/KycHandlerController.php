<?php

namespace Fintech\Ekyc\Http\Controllers;

use Fintech\Core\Traits\ApiResponseTrait;
use Fintech\Ekyc\Facades\Ekyc;
use Fintech\Ekyc\Http\Requests\KycVerificationRequest;
use Fintech\Ekyc\Interfaces\KycVendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class KycHandlerController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly KycVendor $kycVendor)
    {

    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(KycVerificationRequest $request)
    {
        //
    }

    /**
     * @lrd:start
     * this return current enabled kyc vendor login credentials.
     *
     * @lrd:end
     */
    public function credential(): JsonResponse
    {
        $current = config('fintech.ekyc.default');

        $config = config("fintech.ekyc.providers.{$current}");

        $mode = $config['mode'] ?? 'sandbox';

        $credentials = $config[$mode] ?? [];

        return $this->success(['data' => $credentials]);

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

        return $this->success(['data' => $data]);

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
        return $this->success(['data' => ['reference_no' => Ekyc::getReferenceToken()]]);
    }
}

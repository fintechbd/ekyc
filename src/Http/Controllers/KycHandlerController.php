<?php

namespace Fintech\Ekyc\Http\Controllers;

use Fintech\Core\Traits\ApiResponseTrait;
use Fintech\Ekyc\Facades\Ekyc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class KycHandlerController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle the incoming request.
     */
    public function verification(Request $request)
    {
        //        $inputs = $request->validated();

        dd($request->all());

        Ekyc::kycStatus()->create($inputs);
    }

    /**
     * @lrd:start
     * this return any enabled kyc vendor login credentials and options.
     * current available vendors list is avaliable in `/api/ekyc/kyv-vendors`
     *
     * @lrd:end
     */
    public function credential(string $vendor = null): JsonResponse
    {
        if ($vendor == null) {
            $vendor = config('fintech.ekyc.default');
        }

        $config = config("fintech.ekyc.providers.{$vendor}");

        $mode = $config['mode'] ?? 'sandbox';

        $credentials = $config[$mode] ?? [];

        return $this->success([
            'data' => [
                'credentials' => $credentials,
                'options' => $config['options'] ?? []
            ],
            'query' => [
                'vendor' => $vendor
            ]
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

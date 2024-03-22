<?php

namespace Fintech\Ekyc\Http\Controllers;

use App\Http\Controllers\Controller;
use Fintech\Core\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycHandlerController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
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
}

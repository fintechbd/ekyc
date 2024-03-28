<?php

namespace Fintech\Ekyc\Services;

use Fintech\Ekyc\Facades\Ekyc;
use Fintech\Ekyc\Interfaces\KycStatusRepository;
use Fintech\Ekyc\Interfaces\KycVendor;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;

/**
 * Class KycStatusService
 *
 * @property-read KycStatusRepository $kycStatusRepository
 */
class KycStatusService
{
    /**
     * KycStatusService constructor.
     */
    public function __construct(private readonly KycStatusRepository $kycStatusRepository)
    {
    }

    /**
     * @return mixed
     */
    public function list(array $filters = [])
    {
        return $this->kycStatusRepository->list($filters);

    }

    public function create(array $inputs = [])
    {
        return $this->kycStatusRepository->create($inputs);
    }

    /**
     * @throws BindingResolutionException
     * @throws \ErrorException
     */
    public function verify(string $vendor, array $inputs = [])
    {
        $data['reference_no'] = Ekyc::getReferenceToken();
        $data['type'] = 'document';
        $data['attempts'] = 1;
        $data['vendor'] = $vendor;
        $data['kyc_status_data'] = ['inputs' => $inputs];

        $kycVendor = $this->initVendor($vendor);
        $kycVendor->identity($data['reference_no'], $inputs)->verify();
        $data['request'] = Arr::wrap($kycVendor->getPayload());
        $data['response'] = Arr::wrap($kycVendor->getResponse());
        $data['status'] = $kycVendor->getStatus();
        $data['note'] = $kycVendor->getNote();

        return $this->create($data);
    }

    /**
     * @throws BindingResolutionException
     * @throws \ErrorException
     */
    private function initVendor(string $vendor): KycVendor|\Fintech\Ekyc\Abstracts\KycVendor
    {
        $driver = config("fintech.ekyc.providers.{$vendor}.driver");

        if (! $driver) {
            throw new \ErrorException("Missing driver for `{$vendor}` kyc provider.");
        }

        return app()->make($driver);
    }

    public function find($id, $onlyTrashed = false)
    {
        return $this->kycStatusRepository->find($id, $onlyTrashed);
    }

    public function update($id, array $inputs = [])
    {
        return $this->kycStatusRepository->update($id, $inputs);
    }

    public function destroy($id)
    {
        return $this->kycStatusRepository->delete($id);
    }

    public function restore($id)
    {
        return $this->kycStatusRepository->restore($id);
    }

    public function export(array $filters)
    {
        return $this->kycStatusRepository->list($filters);
    }

    public function import(array $filters)
    {
        return $this->kycStatusRepository->create($filters);
    }
}

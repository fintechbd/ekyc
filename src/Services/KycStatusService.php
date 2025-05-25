<?php

namespace Fintech\Ekyc\Services;

use ErrorException;
use Fintech\Core\Abstracts\BaseModel;
use Fintech\Core\Exceptions\VendorNotFoundException;
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
    use \Fintech\Core\Traits\HasFindWhereSearch;

    /**
     * KycStatusService constructor.
     */
    public function __construct(private readonly KycStatusRepository $kycStatusRepository) {}

    /**
     * @return BaseModel
     *
     * @throws BindingResolutionException
     * @throws ErrorException
     */
    public function verify(string $vendor, array $inputs = [])
    {
        $data['reference_no'] = ekyc()->getReferenceToken();
        $data['type'] = 'document';
        $data['attempts'] = 1;
        $data['vendor'] = $vendor;
        $data['kyc_status_data'] = ['inputs' => $inputs];

        $kycVendor = $this->initVendor($vendor);
        $kycVendor->verify($data['reference_no'], $inputs);

        $data['request'] = Arr::wrap($kycVendor->getPayload());
        $data['response'] = Arr::wrap($kycVendor->getResponse());
        $data['status'] = $kycVendor->getStatus();
        $data['note'] = $kycVendor->getNote();

        return $this->create($data);
    }

    /**
     * @throws BindingResolutionException
     * @throws VendorNotFoundException
     */
    private function initVendor(string $vendor): KycVendor|\Fintech\Ekyc\Abstracts\KycVendor
    {
        $driver = config("fintech.ekyc.providers.{$vendor}.driver");

        if (! $driver) {
            throw new VendorNotFoundException(ucfirst($vendor));
        }

        return app()->make($driver);
    }

    public function create(array $inputs = [])
    {
        return $this->kycStatusRepository->create($inputs);
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

    /**
     * @return mixed
     */
    public function list(array $filters = [])
    {
        return $this->kycStatusRepository->list($filters);

    }

    public function import(array $filters)
    {
        return $this->kycStatusRepository->create($filters);
    }
}

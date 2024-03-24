<?php

namespace Fintech\Ekyc\Services;

use Fintech\Ekyc\Facades\Ekyc;
use Fintech\Ekyc\Interfaces\KycStatusRepository;
use Fintech\Ekyc\Interfaces\KycVendor;
use Illuminate\Support\Arr;

/**
 * Class KycStatusService
 *
 * @property-read KycVendor $kycVendor
 * @property-read KycStatusRepository $kycStatusRepository
 */
class KycStatusService
{
    /**
     * KycStatusService constructor.
     */
    public function __construct(private readonly KycStatusRepository $kycStatusRepository,
                                private readonly KycVendor           $kycVendor)
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

    public function verify(string $vendor, array $inputs = [])
    {
        $data['reference_no'] = Ekyc::getReferenceToken();
        $data['type'] = 'document';
        $data['attempts'] = 1;
        $data['vendor'] = $vendor;
        $data['kyc_status_data'] = ['inputs' => $inputs];

        $this->kycVendor->identity($data['reference_no'], $inputs)->verify();
        $data['request'] = Arr::wrap($this->kycVendor->getPayload());
        $data['response'] = Arr::wrap($this->kycVendor->getResponse());
        $data['status'] = $this->kycVendor->getStatus();
        $data['note'] = $this->kycVendor->getNote();

        return $this->create($data);
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

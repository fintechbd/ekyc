<?php

namespace Fintech\Ekyc\Services;

use Fintech\Ekyc\Facades\Ekyc;
use Fintech\Ekyc\Interfaces\KycStatusRepository;
use Fintech\Ekyc\Interfaces\KycVendor;

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
        private readonly KycVendor $kycVendor)
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
        $data['reference_no'] = Ekyc::getReferenceToken();
        $data['type'] = 'document';
        $data['attempts'] = 1;
        $data['vendor'] = 'shufti_pro';
        $data['status'] = 'pending';
        $data['note'] = 'This is a testing request.';
        $data['key_status_data'] = [];

        $this->kycVendor->reference($data['reference_no'])->identity($inputs)->verify();

        $data['request'] = $this->kycVendor->getPayload();
        $data['response'] = $this->kycVendor->getResponse();

        return $this->kycStatusRepository->create($data);
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

<?php

namespace Fintech\Ekyc\Services;


use Fintech\Ekyc\Interfaces\KycStatusRepository;

/**
 * Class KycStatusService
 * @package Fintech\Ekyc\Services
 *
 */
class KycStatusService
{
    /**
     * KycStatusService constructor.
     * @param KycStatusRepository $kycStatusRepository
     */
    public function __construct(KycStatusRepository $kycStatusRepository) {
        $this->kycStatusRepository = $kycStatusRepository;
    }

    /**
     * @param array $filters
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

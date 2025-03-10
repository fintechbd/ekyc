<?php

namespace Fintech\Ekyc\Repositories\Eloquent;

use Fintech\Core\Repositories\EloquentRepository;
use Fintech\Ekyc\Interfaces\KycStatusRepository as InterfacesKycStatusRepository;
use Fintech\Ekyc\Models\KycStatus;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class KycStatusRepository
 */
class KycStatusRepository extends EloquentRepository implements InterfacesKycStatusRepository
{
    public function __construct()
    {
        parent::__construct(config('fintech.ekyc.kyc_status_model', KycStatus::class));
    }

    /**
     * return a list or pagination of items from
     * filtered options
     *
     * @return Paginator|Collection
     */
    public function list(array $filters = [])
    {
        $query = $this->model->newQuery();

        // Searching
        if (! empty($filters['search'])) {
            if (is_numeric($filters['search'])) {
                $query->where($this->model->getKeyName(), 'like', "%{$filters['search']}%");
            } else {
                $query->where('name', 'like', "%{$filters['search']}%");
                $query->orWhere('kyc_status_data', 'like', "%{$filters['search']}%");
            }
        }

        if (! empty($filters['id_not_in'])) {
            $query->whereNotIn($this->model->getKeyName(), (array) $filters['id_not_in']);
        }

        if (! empty($filters['id_in'])) {
            $query->whereIn($this->model->getKeyName(), (array) $filters['id_in']);
        }

        // Display Trashed
        if (isset($filters['trashed']) && $filters['trashed'] === true) {
            $query->onlyTrashed();
        }

        if (! empty($filters['reference_no'])) {
            $query->where('reference_no', $filters['reference_no']);
        }

        // Handle Sorting
        $query->orderBy($filters['sort'] ?? $this->model->getKeyName(), $filters['dir'] ?? 'asc');

        // Execute Output
        return $this->executeQuery($query, $filters);

    }
}

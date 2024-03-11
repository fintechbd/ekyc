<?php

namespace Fintech\Ekyc\Http\Resources;

use Fintech\Core\Enums\Ekyc\KycStatus;
use Fintech\Core\Supports\Constant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KycStatusCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($kycStatus) {
            $data = [
                'id' => $kycStatus->getKey(),
                'user_id' => $kycStatus->user_id ?? null,
                'user_name' => $kycStatus->user->name ?? null,
                'reference_no' => $kycStatus->reference_no ?? null,
                'type' => $kycStatus->type ?? null,
                'attempts' => $kycStatus->attempts ?? 0,
                'vendor' => $kycStatus->vendor ?? null,
                'vendor_name' => $kycStatus->vendor_label ?? null,
                'status' => $kycStatus->status ?? KycStatus::Pending,
                'note' => $kycStatus->note ?? [],
                'request' => $kycStatus->request ?? [],
                'response' => $kycStatus->response ?? [],
                'key_status_data' => $kycStatus->key_status_data ?? [],
                'created_at' => $this->created_at ?? null,
                'updated_at' => $this->updated_at ?? null,
                'deleted_at' => $this->deleted_at ?? null,
            ];

            return $data;
        })->toArray();
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'options' => [
                'dir' => Constant::SORT_DIRECTIONS,
                'per_page' => Constant::PAGINATE_LENGTHS,
                'sort' => ['id', 'name', 'created_at', 'updated_at'],
            ],
            'query' => $request->all(),
        ];
    }
}

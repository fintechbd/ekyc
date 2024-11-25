<?php

namespace Fintech\Ekyc\Http\Resources;

use Fintech\Core\Enums\Ekyc\KycStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KycStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->getKey(),
            'user_id' => $this->user_id ?? null,
            'user_name' => $this->user->name ?? null,
            'reference_no' => $this->reference_no ?? null,
            'type' => $this->type ?? null,
            'attempts' => $this->attempts ?? 0,
            'vendor' => $this->vendor ?? null,
            'vendor_name' => $this->vendor_label ?? null,
            'status' => $this->status ?? KycStatus::Pending,
            'note' => $this->note ?? [],
            'request' => $this->request ?? [],
            'response' => $this->response ?? [],
            'key_status_data' => $this->key_status_data ?? [],
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
            'deleted_at' => $this->deleted_at ?? null,
        ];
    }
}

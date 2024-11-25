<?php

namespace Fintech\Ekyc\Http\Resources;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array $response
 */
class KycVerificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        [$dob, $document_number, $gender, $name, $issue_date, $expiry_date, $sponsor] = $this->getGenericFields($request->route('vendor', config('fintech.ekyc.default')));

        return [
            'query' => [
                'vendor' => $request->route('vendor', config('fintech.ekyc.default')),
            ],
            'generic_fields' => [
                'dob' => $dob,
                'document_number' => $document_number,
                'gender' => $gender,
                'name' => $name,
                'issue_date' => $issue_date,
                'expiry_date' => $expiry_date,
                'sponsor' => $sponsor,
            ],
        ];
    }

    private function getGenericFields(?string $vendor): array
    {
        return match ($vendor) {
            'shufti_pro' => $this->parseShuftiProResponse(),
            'signzy' => $this->parseSignzyResponse(),
            default => [null, null, null, null, null, null],
        };
    }

    private function parseShuftiProResponse(): array
    {
        $response = $this->response['additional_data']['document'] ?? [];

        $dob = $response['proof']['dob'] ?? null;

        $document_number = $response['proof']['document_number'] ?? null;

        if (isset($response['proof']['gender'])) {
            $gender = $response['proof']['gender'];
        } elseif (isset($response['additional_proof']['gender'])) {
            $gender = $response['additional_proof']['gender'];
        } else {
            $gender = 'M';
        }

        $gender = $gender == 'M' ? 'male' : 'female';

        if (isset($response['proof']['full_name'])) {
            $name = $response['proof']['full_name'];
        } elseif (isset($response['additional_proof']['first_name'])) {
            $name = $response['additional_proof']['first_name'].' '.($response['additional_proof']['last_name'] ?? '');
        } elseif (isset($response['proof']['first_name'])) {
            $name = $response['proof']['first_name'].' '.($response['proof']['last_name'] ?? '');
        } else {
            $name = 'Unknown';
        }

        if (isset($response['proof']['issue_date'])) {
            $issue_date = $response['proof']['issue_date'];
        } elseif (isset($response['additional_proof']['issue_date'])) {
            $issue_date = $response['additional_proof']['issue_date'];
        } else {
            $issue_date = '';
        }

        if (isset($response['proof']['expiry_date'])) {
            $expiry_date = $response['proof']['expiry_date'];
        } elseif (isset($response['additional_proof']['expiry_date'])) {
            $expiry_date = $response['additional_proof']['expiry_date'];
        } else {
            $expiry_date = '';
        }

        $sponsor = null;

        return [$dob, $document_number, $gender, $name, $issue_date, $expiry_date, $sponsor];
    }

    private function parseSignzyResponse(): array
    {
        $response = $this->response['result']['results']['extractedFields'] ?? [];

        $idType = $this->response['essentials']['idType'] ?? 'Other Id Card';

        $dob = isset($response['DOB'])
            ? CarbonImmutable::createFromFormat('d/m/Y', $response['DOB'])->format('Y-m-d')
            : null;

        $document_number = match ($idType) {
            'Residence Id Card' => ($response['additionalData']['identityCardNumber'] ?? null),
            default => ($response['number'] ?? null)
        };

        $gender = isset($response['gender'])
            ? ($response['gender'] == 'M' ? 'male' : 'female')
            : null;

        $name = ($response['firstName'] ?? '').' '.($response['lastName'] ?? '');

        $issue_date = isset($response['additionalData']['dateOfIssue'])
            ? CarbonImmutable::createFromFormat('d/m/Y', $response['additionalData']['dateOfIssue'])->format('Y-m-d')
            : null;

        $expiry_date = isset($response['expiryDate'])
            ? CarbonImmutable::createFromFormat('d/m/Y', $response['expiryDate'])->format('Y-m-d')
            : null;

        $sponsor = $response['additionalData']['sponsor'] ?? null;

        return [$dob, $document_number, $gender, $name, $issue_date, $expiry_date, $sponsor];
    }
}

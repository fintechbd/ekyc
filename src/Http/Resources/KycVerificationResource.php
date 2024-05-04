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
        [$dob, $document_number, $gender, $name, $issue_date, $expiry_date] = $this->getGenericFields($request->route('vendor', config('fintech.ekyc.default')));

        return [
            'query' => [
                'vendor' => $request->route('vendor', config('fintech.ekyc.default')),
            ],
            'generic_fields' => [
                'dob' => $dob ?? null,
                'document_number' => $document_number ?? null,
                'gender' => $gender,
                'name' => $name ?? null,
                'issue_date' => $issue_date ?? null,
                'expiry_date' => $expiry_date ?? null,
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

        $gender = isset($response['proof']['gender'])
            ? ($response['proof']['gender'] == 'M' ? 'male' : 'female')
            : null;

        $name = ($response['proof']['first_name'] ?? '').' '.($response['proof']['last_name'] ?? '');

        $issue_date = $response['proof']['issue_date'] ?? null;

        $expiry_date = $response['proof']['expiry_date'] ?? null;

        return [$dob, $document_number, $gender, $name, $issue_date, $expiry_date];
    }

    private function parseSignzyResponse(): array
    {
        $response = $this->response['results']['extractedFields'] ?? [];

        $dob = isset($response['DOB'])
            ? CarbonImmutable::createFromFormat('d/m/Y', $response['DOB'])->format('Y-m-d')
            : null;

        $document_number = $response['number'] ?? null;
        //$document_number = $response['additionalData']['identityCardNumber'] ?? null;

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

        return [$dob, $document_number, $gender, $name, $issue_date, $expiry_date];
    }
}

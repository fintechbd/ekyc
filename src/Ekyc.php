<?php

namespace Fintech\Ekyc;

use Fintech\Core\Facades\Core;
use Fintech\Ekyc\Services\KycStatusService;

class Ekyc
{
    /**
     * @return KycStatusService
     */
    public function kycStatus()
    {
        return app(KycStatusService::class);
    }

    public function getReferenceToken(): string
    {
        $serial = (int) config('fintech.ekyc.reference_count');

        $prefix = strtoupper(config('fintech.ekyc.reference_prefix', 'KYC'));

        $length = (int) config('fintech.core.entry_number_length', 20) - strlen($prefix);

        Core::setting()->setValue('ekyc', 'reference_count', $serial + 1, 'integer');

        return $prefix.str_pad(
            (string) $serial,
            $length,
            config('fintech.core.entry_number_fill', '0'),
            STR_PAD_LEFT
        );
    }

    //** Crud Service Method Point Do not Remove **//

}

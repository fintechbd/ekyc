<?php

namespace Fintech\Ekyc;

class Ekyc
{
    /**
     * @return \Fintech\Ekyc\Services\KycStatusService
     */
    public function kycStatus()
    {
        return app(\Fintech\Ekyc\Services\KycStatusService::class);
    }

    //** Crud Service Method Point Do not Remove **//

}

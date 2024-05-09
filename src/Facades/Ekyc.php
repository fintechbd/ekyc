<?php

namespace Fintech\Ekyc\Facades;

use Fintech\Ekyc\Services\KycStatusService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static KycStatusService kycStatus()
 * @method static string getReferenceToken()
 *                                           // Crud Service Method Point Do not Remove //
 *
 * @see \Fintech\Ekyc\Ekyc
 */
class Ekyc extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Fintech\Ekyc\Ekyc::class;
    }
}

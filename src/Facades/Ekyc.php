<?php

namespace Fintech\Ekyc\Facades;

use Fintech\Ekyc\Services\KycStatusService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Support\Collection|KycStatusService kycStatus(array $filters = null)
 * @method static \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Support\Collection|string getReferenceToken(array $filters = null)
 *                                                                                                                                          // Crud Service Method Point Do not Remove //
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

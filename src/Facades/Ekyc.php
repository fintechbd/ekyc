<?php

namespace Fintech\Ekyc\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * // Crud Service Method Point Do not Remove //
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

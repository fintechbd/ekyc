<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "API" middleware group. Enjoy building your API!
|
*/
if (Config::get('fintech.ekyc.enabled')) {
    Route::prefix('ekyc')->name('ekyc.')
        ->middleware(config('fintech.auth.middleware'))
        ->group(function () {
            Route::apiResource('kyc-statuses', \Fintech\Ekyc\Http\Controllers\KycStatusController::class);
            Route::post('kyc-statuses/{kyc_status}/restore', [\Fintech\Ekyc\Http\Controllers\KycStatusController::class, 'restore'])->name('kyc-statuses.restore');
            //DO NOT REMOVE THIS LINE//
            Route::withoutMiddleware(config('fintech.auth.middleware'))->group(function () {
                Route::post('kyc-verification', \Fintech\Ekyc\Http\Controllers\KycHandlerController::class)->name('kyc-verification');
                Route::post('kyc-credential', [\Fintech\Ekyc\Http\Controllers\KycHandlerController::class, 'credential'])->name('kyc-credential');
                Route::get('kyc-vendors', [\Fintech\Ekyc\Http\Controllers\KycHandlerController::class, 'vendor'])->name('kyc-vendors');
                Route::get('kyc-token', [\Fintech\Ekyc\Http\Controllers\KycHandlerController::class, 'token'])->name('kyc-vendors');
            });
        });
}

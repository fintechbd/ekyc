<?php

use Fintech\RestApi\Http\Controllers\Ekyc\KycHandlerController;
use Fintech\RestApi\Http\Controllers\Ekyc\KycStatusController;
use Fintech\RestApi\Http\Controllers\Ekyc\VendorSyncController;
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
    Route::prefix(config('fintech.ekyc.root_prefix', 'api/'))->middleware(['api'])->group(function () {
        Route::prefix('ekyc')->name('ekyc.')
            ->middleware(config('fintech.auth.middleware'))
            ->group(function () {
                Route::apiResource('kyc-statuses', KycStatusController::class);
                //             Route::post('kyc-statuses/{kyc_status}/restore', [KycStatusController::class, 'restore'])->name('kyc-statuses.restore');
                Route::get('sync-credentials/{vendor}', VendorSyncController::class)->name('kyc.sync-credentials');
                Route::withoutMiddleware(config('fintech.auth.middleware'))->group(function () {
                    Route::post('verification/{vendor?}', [KycHandlerController::class, 'verification'])->name('kyc.verification');
                    Route::get('credentials/{vendor?}', [KycHandlerController::class, 'credential'])->name('kyc.credential');
                    Route::get('vendors', [KycHandlerController::class, 'vendor'])->name('kyc.vendors');
                    Route::get('reference-token', [KycHandlerController::class, 'token'])->name('kyc.vendors');
                    Route::any('status-change-callback', [KycHandlerController::class, 'statusCallback'])->name('kyc.status-change-callback');
                });
                //DO NOT REMOVE THIS LINE//
            });
    });
}

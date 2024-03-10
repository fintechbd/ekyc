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
    Route::prefix('ekyc')->name('ekyc.')->group(function () {
        Route::post('kyc-initialize', \Fintech\Ekyc\Http\Controllers\KycHandlerController::class)->name('kyc-initialize');
        Route::apiResource('kyc-statuses', \Fintech\Ekyc\Http\Controllers\KycStatusController::class);
        Route::post('kyc-statuses/{kyc_status}/restore', [\Fintech\Ekyc\Http\Controllers\KycStatusController::class, 'restore'])->name('kyc-statuses.restore');

        //DO NOT REMOVE THIS LINE//
    });
}

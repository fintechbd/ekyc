<?php

// config for Fintech/Ekyc
return [

    /*
    |--------------------------------------------------------------------------
    | Enable Module APIs
    |--------------------------------------------------------------------------
    | This setting enable the API will be available or not
    */
    'enabled' => env('PACKAGE_EKYC_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | KYC Vendor
    |--------------------------------------------------------------------------
    | When KYC is initialed which vendor will be used to proceed.
    */
    'default' => 'manual',

    /*
    |--------------------------------------------------------------------------
    | Ekyc Group Root Prefix
    |--------------------------------------------------------------------------
    |
    | This value will be added to all your routes from this package
    | Example: APP_URL/{root_prefix}/api/ekyc/action
    |
    | Note: while adding prefix add closing ending slash '/'
    */

    'root_prefix' => 'test/',

    'providers' => [
        'manual' => [
            'mode' => 'live',
            'driver' => Fintech\Ekyc\Vendors\AdminVerify::class,
            'live' => [
                'endpoint' => 'https://api.shuftipro.com',
                'username' => '7106UAT',
                'password' => '7106@Pass',
            ],
            'sandbox' => [
                'endpoint' => 'https://api.shuftipro.com',
                'username' => '7086UAT',
                'password' => '7086@Pass',
            ],
        ],
        'shufti_pro' => [
            'mode' => 'sandbox',
            'driver' => Fintech\Ekyc\Vendors\ShuftiPro::class,
            'live' => [
                'endpoint' => 'https://api.shuftipro.com',
                'username' => env('PACKAGE_EKYC_SHUFTIPRO_ID', null),
                'password' => env('PACKAGE_EKYC_SHUFTIPRO_SECRET', null),
            ],
            'sandbox' => [
                'endpoint' => 'https://api.shuftipro.com',
                'username' => env('PACKAGE_EKYC_SHUFTIPRO_ID', null),
                'password' => env('PACKAGE_EKYC_SHUFTIPRO_SECRET', null),
            ],
        ],
        'signzy' => [
            'mode' => 'sandbox',
            'driver' => Fintech\Ekyc\Vendors\Signzy::class,
            'live' => [
                'endpoint' => 'https://api.shuftipro.com',
                'username' => env('PACKAGE_EKYC_SHUFTIPRO_ID', null),
                'password' => env('PACKAGE_EKYC_SHUFTIPRO_SECRET', null),
            ],
            'sandbox' => [
                'endpoint' => 'https://api.shuftipro.com',
                'username' => env('PACKAGE_EKYC_SHUFTIPRO_ID', null),
                'password' => env('PACKAGE_EKYC_SHUFTIPRO_SECRET', null),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | KycStatus Model
    |--------------------------------------------------------------------------
    |
    | This value will be used to across system where model is needed
    */
    'kyc_status_model' => \Fintech\Ekyc\Models\KycStatus::class,

    //** Model Config Point Do not Remove **//

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | This value will be used across systems where a repositoy instance is needed
    */

    'repositories' => [
        \Fintech\Ekyc\Interfaces\KycStatusRepository::class => \Fintech\Ekyc\Repositories\Eloquent\KycStatusRepository::class,

        //** Repository Binding Config Point Do not Remove **//
    ],

];

<?php

// config for Fintech/Ekyc
use Fintech\Ekyc\Models\KycStatus;
use Fintech\Ekyc\Repositories\Eloquent\KycStatusRepository;

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
    'default' => env('PACKAGE_EKYC_DRIVER', 'manual'),

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

    /*
    |--------------------------------------------------------------------------
    | KYC Service Providers
    |--------------------------------------------------------------------------
    |
    | This value will be added to all your routes from this package
    |
    */

    'providers' => [
        'manual' => [
            'mode' => 'live',
            'driver' => Fintech\Ekyc\Services\Vendors\AdminVerify::class,
            'countries' => [],
            'live' => [
                'endpoint' => '',
                'username' => '',
                'password' => '',
            ],
            'sandbox' => [
                'endpoint' => '',
                'username' => '',
                'password' => '',
            ],
            'options' => [],
        ],
        'shufti_pro' => [
            'mode' => env('PACKAGE_EKYC_SHUFTIPRO_MODE', 'sandbox'),
            'driver' => Fintech\Ekyc\Services\Vendors\ShuftiPro::class,
            'countries' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15,
                16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30,
                31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45,
                46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60,
                61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75,
                76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90,
                91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104,
                105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116,
                117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128,
                129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140,
                141, 142, 143, 144, 145, 146, 147, 148, 149, 150, 151, 152,
                153, 154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164,
                165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176,
                177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 188,
                189, 190, 191, 192, 193, 194, 195, 196, 197, 198, 199, 200,
                201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212,
                213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224,
                225, 226, 227, 228, 229, 230, 232, 233, 234, 235, 236, 237,
                238, 239, 240, 241, 242, 243, 244, 245, 246, 247, 248, 249,
                250],
            'live' => [
                'endpoint' => 'https://api.shuftipro.com',
                'client_id' => env('PACKAGE_EKYC_SHUFTIPRO_ID', null),
                'secret_key' => env('PACKAGE_EKYC_SHUFTIPRO_SECRET', null),
            ],
            'sandbox' => [
                'endpoint' => 'https://api.shuftipro.com',
                'client_id' => env('PACKAGE_EKYC_SHUFTIPRO_ID', null),
                'secret_key' => env('PACKAGE_EKYC_SHUFTIPRO_SECRET', null),
            ],
            'options' => [
                'language' => strtoupper(config('app.locale', '')),
                'verification_mode' => 'any',
                'allow_offline' => '1',
                'allow_online' => '0',
                'allow_retry' => '1',
                'show_consent' => '0',
                'decline_on_single_step' => '1',
                'enhanced_originality_checks' => '1',
                'manual_review' => '0',
            ],
        ],
        'signzy' => [
            'mode' => env('PACKAGE_EKYC_SIGNZY_MODE', 'sandbox'),
            'driver' => Fintech\Ekyc\Services\Vendors\Signzy::class,
            'countries' => [231],
            'live' => [
                'endpoint' => 'https://signzy.tech/api/v2/patrons',
                'username' => env('PACKAGE_EKYC_SIGNZY_USERNAME', null),
                'password' => env('PACKAGE_EKYC_SIGNZY_PASSWORD', null),
            ],
            'sandbox' => [
                'endpoint' => 'https://preproduction.signzy.tech/api/v2/patrons',
                'username' => env('PACKAGE_EKYC_SIGNZY_USERNAME', null),
                'password' => env('PACKAGE_EKYC_SIGNZY_PASSWORD', null),
            ],
            'options' => [
                'expired_at' => null,
                'access_token' => null,
                'patron_id' => null,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | KYC Reference Token
    |--------------------------------------------------------------------------
    |
    | This value will be added to all your routes from this package
    |
    */

    'reference_prefix' => 'KYC',
    'reference_count' => 0,

    /*
    |--------------------------------------------------------------------------
    | KycStatus Model
    |--------------------------------------------------------------------------
    |
    | This value will be used to across system where model is needed
    */
    'kyc_status_model' => KycStatus::class,

    //** Model Config Point Do not Remove **//

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | This value will be used across systems where a repository instance is needed
    */

    'repositories' => [
        \Fintech\Ekyc\Interfaces\KycStatusRepository::class => KycStatusRepository::class,

        //** Repository Binding Config Point Do not Remove **//
    ],

];

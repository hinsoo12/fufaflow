<?php

/*
|--------------------------------------------------------------------------
| FufaFlow product configuration
|--------------------------------------------------------------------------
|
| Defaults applied when a business (tenant) is created. These are defaults,
| not business rules: every tenant stores its own values and can change them.
|
*/

return [

    'defaults' => [
        'country' => 'ET',
        'currency' => 'ETB',
        'timezone' => 'Africa/Addis_Ababa',
        'locale' => 'en',
        'trial_days' => (int) env('FUFAFLOW_TRIAL_DAYS', 30),
        'grace_days' => 3,
        'renewal_window_days' => 7,
        'renewal_after_expiry' => 'restart', // restart | continue
    ],

    /*
    | Currencies supported for a tenant. "exponent" is the ISO 4217 number of
    | minor-unit digits. Money is always stored as integer minor units, so
    | never assume two decimals.
    */
    'currencies' => [
        'ETB' => ['name' => 'Ethiopian Birr', 'exponent' => 2],
        'USD' => ['name' => 'US Dollar', 'exponent' => 2],
        'EUR' => ['name' => 'Euro', 'exponent' => 2],
        'KES' => ['name' => 'Kenyan Shilling', 'exponent' => 2],
        'UGX' => ['name' => 'Ugandan Shilling', 'exponent' => 0],
        'TZS' => ['name' => 'Tanzanian Shilling', 'exponent' => 2],
        'RWF' => ['name' => 'Rwandan Franc', 'exponent' => 0],
        'GHS' => ['name' => 'Ghanaian Cedi', 'exponent' => 2],
        'NGN' => ['name' => 'Nigerian Naira', 'exponent' => 2],
        'AED' => ['name' => 'UAE Dirham', 'exponent' => 2],
    ],

    'countries' => [
        'ET' => ['name' => 'Ethiopia', 'currency' => 'ETB', 'timezone' => 'Africa/Addis_Ababa'],
        'KE' => ['name' => 'Kenya', 'currency' => 'KES', 'timezone' => 'Africa/Nairobi'],
        'UG' => ['name' => 'Uganda', 'currency' => 'UGX', 'timezone' => 'Africa/Kampala'],
        'TZ' => ['name' => 'Tanzania', 'currency' => 'TZS', 'timezone' => 'Africa/Dar_es_Salaam'],
        'RW' => ['name' => 'Rwanda', 'currency' => 'RWF', 'timezone' => 'Africa/Kigali'],
        'GH' => ['name' => 'Ghana', 'currency' => 'GHS', 'timezone' => 'Africa/Accra'],
        'NG' => ['name' => 'Nigeria', 'currency' => 'NGN', 'timezone' => 'Africa/Lagos'],
        'AE' => ['name' => 'United Arab Emirates', 'currency' => 'AED', 'timezone' => 'Asia/Dubai'],
        'US' => ['name' => 'United States', 'currency' => 'USD', 'timezone' => 'America/New_York'],
    ],

    'timezones' => [
        'Africa/Addis_Ababa',
        'Africa/Nairobi',
        'Africa/Kampala',
        'Africa/Dar_es_Salaam',
        'Africa/Kigali',
        'Africa/Accra',
        'Africa/Lagos',
        'Africa/Johannesburg',
        'Africa/Cairo',
        'Asia/Dubai',
        'Europe/London',
        'America/New_York',
        'UTC',
    ],

    /*
    | Payment methods seeded for a new tenant, per country. Tenants can rename
    | or deactivate them later. The "kind" is a platform-level category used
    | for reporting; it never drives business logic.
    */
    'payment_methods' => [
        'ET' => [
            ['name' => 'Cash', 'kind' => 'cash'],
            ['name' => 'Bank transfer', 'kind' => 'bank_transfer'],
            ['name' => 'Telebirr', 'kind' => 'mobile_money'],
            ['name' => 'CBE Birr', 'kind' => 'mobile_money'],
            ['name' => 'M-Pesa', 'kind' => 'mobile_money'],
            ['name' => 'Other', 'kind' => 'other'],
        ],
        'KE' => [
            ['name' => 'Cash', 'kind' => 'cash'],
            ['name' => 'M-Pesa', 'kind' => 'mobile_money'],
            ['name' => 'Bank transfer', 'kind' => 'bank_transfer'],
            ['name' => 'Card', 'kind' => 'card'],
            ['name' => 'Other', 'kind' => 'other'],
        ],
        'default' => [
            ['name' => 'Cash', 'kind' => 'cash'],
            ['name' => 'Bank transfer', 'kind' => 'bank_transfer'],
            ['name' => 'Mobile money', 'kind' => 'mobile_money'],
            ['name' => 'Card', 'kind' => 'card'],
            ['name' => 'Other', 'kind' => 'other'],
        ],
    ],

    'invitations' => [
        'expire_days' => 7,
    ],

];

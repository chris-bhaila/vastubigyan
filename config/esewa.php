<?php

return [

    /*
    |--------------------------------------------------------------------------
    | eSewa Environment
    |--------------------------------------------------------------------------
    |
    | "sandbox" or "production". Selects the default form and status URLs
    | below when ESEWA_FORM_URL / ESEWA_STATUS_URL are not set explicitly.
    |
    */

    'env' => env('ESEWA_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Merchant Product Code
    |--------------------------------------------------------------------------
    |
    | "EPAYTEST" is eSewa's published sandbox product code. Production needs
    | the real merchant code issued by eSewa.
    |
    */

    'product_code' => env('ESEWA_PRODUCT_CODE', 'EPAYTEST'),

    /*
    |--------------------------------------------------------------------------
    | Merchant Secret
    |--------------------------------------------------------------------------
    |
    | Used to sign checkout requests and verify callback signatures. Never
    | log this value.
    |
    */

    'secret' => env('ESEWA_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | ePay v2 Form URL
    |--------------------------------------------------------------------------
    |
    | Where the browser POSTs the checkout form.
    |
    */

    'form_url' => env('ESEWA_FORM_URL') ?: (env('ESEWA_ENV', 'sandbox') === 'production'
        ? 'https://epay.esewa.com.np/api/epay/main/v2/form'
        : 'https://rc-epay.esewa.com.np/api/epay/main/v2/form'),

    /*
    |--------------------------------------------------------------------------
    | Transaction Status API URL
    |--------------------------------------------------------------------------
    |
    | Server-to-server status check. The sandbox URL is not published
    | alongside the form URL as clearly as production's; confirm it against
    | eSewa's developer docs before relying on it for real sandbox traffic.
    |
    */

    'status_url' => env('ESEWA_STATUS_URL') ?: (env('ESEWA_ENV', 'sandbox') === 'production'
        ? 'https://epay.esewa.com.np/api/epay/transaction/status/'
        : 'https://rc.esewa.com.np/api/epay/transaction/status/'),

];

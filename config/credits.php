<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Credit Balance Warnings
    |--------------------------------------------------------------------------
    |
    | The balance page shows a warning banner when the user's available
    | credits are at or below the threshold, or their soonest-expiring
    | batch expires within the configured number of days.
    |
    */

    'low_balance_threshold' => 2,

    'expiry_warning_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Credit Restoration
    |--------------------------------------------------------------------------
    |
    | When a failed analysis restores its spent credit and its original
    | batch has already expired, the replacement batch granted instead
    | expires this many days from now.
    |
    */

    'restore_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Manual Grant Limits
    |--------------------------------------------------------------------------
    |
    | Bounds enforced when a super admin manually grants credits to a user
    | from the admin panel.
    |
    */

    'grant_count_min' => 1,
    'grant_count_max' => 100,

    'grant_expiry_days_min' => 1,
    'grant_expiry_days_max' => 730,
    'grant_expiry_days_default' => 365,

];

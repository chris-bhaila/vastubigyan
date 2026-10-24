<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Calendar Assumption
    |--------------------------------------------------------------------------
    |
    | ASSUMPTION: every date and year collected by the analysis intake form
    | (birth date, visit date, construction year, etc.) is Gregorian (AD),
    | not Bikram Sambat (BS). If the business later needs BS input, the
    | fields, validation and storage here all need to change together.
    |
    */

    'genders' => ['male', 'female', 'other'],

    'nakshatras' => [
        'Ashwini', 'Bharani', 'Krittika', 'Rohini', 'Mrigashira', 'Ardra',
        'Punarvasu', 'Pushya', 'Ashlesha', 'Magha', 'Purva Phalguni',
        'Uttara Phalguni', 'Hasta', 'Chitra', 'Swati', 'Vishakha',
        'Anuradha', 'Jyeshtha', 'Mula', 'Purva Ashadha', 'Uttara Ashadha',
        'Shravana', 'Dhanishta', 'Shatabhisha', 'Purva Bhadrapada',
        'Uttara Bhadrapada', 'Revati',
    ],

    'months' => [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ],

    'birth_year_min' => 1900,
    'constructed_year_min' => 1900,

    'files' => [
        'mimes' => ['jpg', 'jpeg', 'png'],
        'max_kb' => 5120,
    ],

];

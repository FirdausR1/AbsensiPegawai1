<?php

// Tambahkan array di bawah ini ke dalam file config/services.php Laravel kamu
// (file ini sudah ada bawaan Laravel — jangan replace semuanya, cukup gabungkan key berikut)

return [

    'google_sheets' => [
        'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
    ],

    'google_apps_script' => [
        'url' => env('GOOGLE_APPS_SCRIPT_URL', ''),
    ],

    'holiday' => [
        'api_url' => env('HOLIDAY_API_URL', 'https://date.nager.at/api/v3/PublicHolidays'),
        'country_code' => env('HOLIDAY_COUNTRY_CODE', 'ID'),
    ],

];

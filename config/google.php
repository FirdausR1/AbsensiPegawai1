<?php

return [
    'application_name' => env('GOOGLE_APPLICATION_NAME', 'Attendance App'),
    'client_id'        => env('GOOGLE_CLIENT_ID', ''),
    'client_secret'    => env('GOOGLE_CLIENT_SECRET', ''),
    'redirect_uri'     => env('GOOGLE_REDIRECT', ''),
    'scopes'           => [\Google\Service\Sheets::SPREADSHEETS, \Google\Service\Drive::DRIVE],
    'access_type'      => 'offline',
    'approval_prompt'  => 'auto',
    'prompt'           => 'consent',

    'service' => [
        'enable' => env('GOOGLE_SERVICE_ENABLED', true),
        'file'   => env('GOOGLE_SHEETS_APPLICATION_CREDENTIALS_PATH', storage_path('app/google/service-account.json')),
    ],

    'config' => [],
];

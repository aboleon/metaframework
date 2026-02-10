<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Optional User Type Segregation
    |--------------------------------------------------------------------------
    |
    | Keep this disabled unless your app uses a single users table for
    | multiple authentication domains (for example: system and account users).
    |
    */
    'enabled' => false,

    // Column used to discriminate user domains inside the users table.
    'column' => 'type',

    // Supported type keys.
    'values' => [
        'system',
        'account',
    ],

    // Fallback type when no explicit type is provided.
    'default' => 'system',

    // Optional guard -> type mapping used by UserTypes::addToCredentials().
    'guards' => [
        'web' => 'system',
        'account' => 'account',
    ],
];

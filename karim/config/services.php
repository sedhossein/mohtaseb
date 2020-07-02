<?php

return [
    'wallet' => [
        'provider' => 'jib',
        'providers' => [
            'jib' => [
                'base_url' => env('JIB_SERVICE_BASE_URL', 'http://jib'),
                'timeout' => env('JIB_SERVICE_TIMEOUT', 4),
            ],
        ],
    ],
];

<?php

return [
    'ewdb' => [
        'url' => env('EW_EWDB_URL', 'http://localhost:8001'),
        'token' => env('EW_EWDB_TOKEN', ''),
    ],

    'queue' => [
        'url' => env('EW_QUEUE_URL', 'http://localhost:62020'),
    ],
];
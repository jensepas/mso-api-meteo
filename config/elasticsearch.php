<?php

return [
    'hosts' => [
        [
            'host' => env('ELASTICSEARCH_HOST'),
            'port' => env('ELASTICSEARCH_PORT'),
            'user' => env('ELASTICSEARCH_USER'),
            'pass' => env('ELASTICSEARCH_PASS'),
        ],
    ],
];

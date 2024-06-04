<?php

return [
    'hosts' => [
        [
            'host' => env('ELASTICSEARCH_HOST'),
            'port' => env('ELASTICSEARCH_PORT'),
            'user' => env('ELASTICSEARCH_USERNAME'),
            'pass' => env('ELASTICSEARCH_PASSWORD'),
        ],
    ],
];

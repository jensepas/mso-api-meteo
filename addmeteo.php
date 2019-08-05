<?php

require_once('vendor/autoload.php');


$index = 'meteo';
$apiKey = 'AZERTYUEEEEE';
$response = [];

function randomDateInRange(DateTime $start, DateTime $end)
{
    $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
    $randomDate = new DateTime();
    $randomDate->setTimestamp($randomTimestamp);
    return $randomTimestamp;
}


$client = Elasticsearch\ClientBuilder::create()
    ->setHosts(["localhost:9200"])
    ->build();


echo 'Suppression de l\'index' . '<br>';
$params = ['index' => $index];
$response = $client->indices()->delete($params);

print_r($response);
echo '<br><br><br>';


echo 'Création de l\'index' . '<br>';
$params = [
    'index' => $index,
    'body' => [
        'settings' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
            'refresh_interval' => '1s'
        ],

        'mappings' => [
            'properties' => [
                'apiKey' => [
                    'type' => 'keyword',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword'
                        ]
                    ]
                ],
                'libelle' => [
                    'type' => 'keyword',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword'
                        ]
                    ]
                ],
                'date' => [
                    'type' => 'date',
                    'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword'
                        ]
                    ]
                ],
                'value' => [
                    'type' => 'float',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword'
                        ]
                    ]
                ],
                'unity' => [
                    'type' => 'text',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword'
                        ]
                    ]
                ]
            ]
        ]
    ]
];


$client->indices()->create($params);

print_r($response);
echo '<br><br><br>';

exit;
$start = new DateTime('2019-01-01');
$end = new DateTime(date('Y-m-d 23:59:59'));


$params = [
    'body' => []
];

for ($i = 1; $i <= 200000; $i++) {
    $params['body'][] = [
        'index' => [
            '_index' => $index
        ]
    ];
    $date = randomDateInRange($start, $end);

    $params['body'][] = [
        'apiKey' => $apiKey,
        'date' => $date,
        'libelle' => 'pressur',
        'value' => rand(950, 1024),
        'unity' => 'mb'
    ];


    $params['body'][] = [
        'index' => [
            '_index' => $index
        ]
    ];

    $params['body'][] = [
        'apiKey' => $apiKey,
        'date' => $date,
        'libelle' => 'temp',
        'value' => rand(-32, 32),
        'unity' => '°c'
    ];


    $params['body'][] = [
        'index' => [
            '_index' => $index
        ]
    ];

    $params['body'][] = [
        'apiKey' => $apiKey,
        'date' => $date,
        'libelle' => 'humidity',
        'value' => rand(15, 85),
        'unity' => '%'
    ];

    // Every 1000 documents stop and send the bulk request
    if ($i % 1000 == 0) {
        $responses = $client->bulk($params);

        // erase the old bulk request
        $params = ['body' => []];

        print_r($responses);
        echo '<br>';
        // unset the bulk response when you are done to save memory
        unset($responses);
    }
}

// Send the last batch if it exists
if (!empty($params['body'])) {
    $responses = $client->bulk($params);
}
print_r($response);
echo '<br><br><br>';

<pre>



<?php
require_once('vendor/autoload.php');

$index = 'air_measurements';

$apiKey = 'dKim8c9Vwsdal7JTnqTb5Tv5K2o6IQbKkaZZ9lZAg-s';

$location = [48.770938, 2.070463];

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
            'refresh_interval' => '-1'
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

$params = [
    'index' => $index,
    'body' => [
        "settings" => [
        "number_of_shards" => 1,
        "number_of_replicas" => 0,
        "refresh_interval" => "1s"
        ],
        "mappings" => [
            "properties" => [
                "apiKey" => ["type" => "keyword"],
                "timestamp" => ["type" => "date"],
                "location" => ["type" => "geo_point"],
                "measurement" => [
                    "properties" => [
                        "value" => ["type" => "double"],
                        "label" => ["type" => "keyword"],
                        "unit" => ["type" => "keyword"]
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
$start = new DateTime('2019-07-01');
$end = new DateTime(date('Y-m-d 23:59:59'));


$params = [
    'body' => []
];

for ($i = 1; $i <= 20000; $i++) {
    $params['body'][] = [
        'index' => [
            '_index' => $index
        ]
    ];
    $date = randomDateInRange($start, $end);

    $params['body'][] = [
        'apiKey' => $apiKey,
        'timestamp' => $date,
        'location' => $location,
        'measurement.label' => 'pressure',
        'measurement.value' => rand(950, 1024),
        'measurement.unit' => 'hpa'
    ];

    $params['body'][] = [
        'index' => [
            '_index' => $index
        ]
    ];

    $params['body'][] = [
        'apiKey' => $apiKey,
        'timestamp' => $date,
        'location' => $location,
        'measurement.label' => 'temperature',
        'measurement.value' => rand(-32, 32),
        'measurement.unit' => 'c'
    ];

    $params['body'][] = [
        'index' => [
            '_index' => $index
        ]
    ];

    $params['body'][] = [
        'apiKey' => $apiKey,
        'timestamp' => $date,
        'location' => $location,
        'measurement.label' => 'hygrometry',
        'measurement.value' => rand(15, 85),
        'measurement.unit' => '%'
    ];

    $params['body'][] = [
        'index' => [
            '_index' => $index
        ]
    ];

    $params['body'][] = [
        'apiKey' => $apiKey,
        'timestamp' => $date,
        'location' => $location,
        'measurement.label' => 'dust',
        'measurement.value' => rand(15, 85),
        'measurement.unit' => 'ug/m3'
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

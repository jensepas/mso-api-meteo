<?php

namespace App\Console\Commands;

use Elasticsearch\Client;
use Illuminate\Console\Command;

class ElasticsearchCreateIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Index Elasticsearch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param Client $client
     * @return void
     */
    public function handle(Client $client): void
    {
        if ($client->ping()) {

            $index = 'air_measurements';

            $this->info('Suppression de l\'index');
            $params = ['index' => $index];
            $response = $client->indices()->delete($params);

            print_r($response);


            $this->info('Création de l\'index');

            $params = [
                'index' => $index,
                'body'  => [
                    "settings" => [
                        "number_of_shards"   => 1,
                        "number_of_replicas" => 0,
                        "refresh_interval"   => "1s"
                    ],
                    "mappings" => [
                        "properties" => [
                            "apiKey"      => [
                                "type"   => "keyword"
                            ],
                            "label"      => [
                                "type"   => "keyword"
                            ],
                            "public"      => [
                                "type"   => "bool"
                            ],
                            "timestamp"   => [
                                "type" => "date"
                            ],
                            "location"    => [
                                "type" => "geo_point"

                            ],
                            "measurement" => [
                                "properties" => [
                                    "value" => [
                                        "type" => "double"
                                    ],
                                    "label" => [
                                        "type"   => "text",
                                        "fields" => [
                                            "keyword" => [
                                                "type"         => "keyword",
                                                "ignore_above" => 256
                                            ]
                                        ]
                                    ],
                                    "unit"  => [
                                        "type"   => "text",
                                        "fields" => [
                                            "keyword" => [
                                                "type"         => "keyword",
                                                "ignore_above" => 256
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $response = $client->indices()->create($params);

            print_r($response);
            return;
        }

        $this->error('Could not connect to Elasticsearch.');
    }
}

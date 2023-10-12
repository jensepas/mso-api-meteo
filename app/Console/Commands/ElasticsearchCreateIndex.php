<?php

namespace App\Console\Commands;

use Elastic\Elasticsearch\Client;
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
     * @throws \Exception
     */
    public function handle(Client $client): void
    {
        if (!is_null($client->ping())) {
            $index = 'air_measurements';

            $this->info('Suppression de l\'index');
            $params = ['index' => $index];
            $client->indices()->delete($params);

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
                                "type"   => "boolean"
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

            $client->indices()->create($params);

            return;
        }

        $this->error('Could not connect to Elasticsearch.');
    }
}

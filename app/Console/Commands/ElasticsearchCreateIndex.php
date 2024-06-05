<?php

namespace App\Console\Commands;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
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
     * @throws \Exception
     */
    public function handle(Client $client): void
    {
        if ($this->isElasticsearchAvailable($client)) {
            $index = env('ELASTICSEARCH_INDEX');
            $this->deleteIndex($client, $index);
            $this->createIndex($client, $index);
        }
    }

    /**
     * Check if Elasticsearch is available.
     */
    private function isElasticsearchAvailable(Client $client): bool
    {
        $ping = null;
        try {
            $ping = $client->ping();
        } catch (ClientResponseException|ServerResponseException) {
            $this->error('Could not connect to Elasticsearch.');
        }

        return ! is_null($ping);
    }

    /**
     * Delete the Elasticsearch index if it exists.
     */
    private function deleteIndex(Client $client, string $index): void
    {
        $this->info('Deleting index Elasticsearch');
        $params = ['index' => $index];
        try {
            $client->indices()->delete($params);
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            $this->error('Error deleting index Elasticsearch : '.$e->getMessage());
        }
    }

    /**
     * Create the Elasticsearch index.
     */
    private function createIndex(Client $client, string $index): void
    {
        $this->info('Creating index Elasticsearch');

        $params = [
            'index' => $index,
            'body' => [
                'settings' => $this->getIndexSettings(),
                'mappings' => $this->getIndexMappings(),
            ],
        ];

        try {
            $client->indices()->create($params);
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            $this->error('Error creating index : '.$e->getMessage());
        }
    }

    /**
     * Get the settings for the Elasticsearch index.
     */
    private function getIndexSettings(): array
    {
        return [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
            'refresh_interval' => '1s',
        ];
    }

    /**
     * Get the mappings for the Elasticsearch index.
     */
    private function getIndexMappings(): array
    {
        return [
            'properties' => [
                'apiKey' => ['type' => 'keyword'],
                'label' => ['type' => 'keyword'],
                'public' => ['type' => 'boolean'],
                'timestamp' => ['type' => 'date'],
                'location' => ['type' => 'geo_point'],
                'measurement' => [
                    'properties' => [
                        'value' => ['type' => 'double'],
                        'label' => [
                            'type' => 'text',
                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword',
                                    'ignore_above' => 256,
                                ],
                            ],
                        ],
                        'unit' => [
                            'type' => 'text',
                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword',
                                    'ignore_above' => 256,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}

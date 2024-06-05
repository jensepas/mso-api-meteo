<?php

namespace App\Console\Commands;

use DateTime;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Console\Command;

class ElasticsearchEnrichIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:enrich';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enrich Index Elasticsearch';

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
            $this->info('Enrich index Elasticsearch');
            $index = env('ELASTICSEARCH_INDEX');
            $start = new DateTime(date('Y-m-d 00:00:01'));
            $end = new DateTime(date('Y-m-d 23:59:59'));
            $params = ['body' => []];

            for ($i = 1; $i <= 500000; $i++) {
                $date = $this->randomDateInRange($start, $end);

                $params['body'] = array_merge(
                    $params['body'],
                    $this->prepareBulkData(
                        $index,
                        'KEY_EXT',
                        $date,
                        'Capteur extérieur',
                        [48.770938, 2.070463],
                        $this->getOutdoorMeasurements()
                    )
                );
                $params['body'] = array_merge(
                    $params['body'],
                    $this->prepareBulkData(
                        $index,
                        'KEY_INT',
                        $date,
                        'Capteur Maurepas',
                        [48.770938, 2.050467],
                        $this->getIndoorMeasurements()
                    )
                );

                if ($i % 1000 == 0) {
                    $this->sendBulkRequest($client, $params);
                    $params['body'] = []; // Reset the body after sending
                }
            }
        }
    }
    /**
     * Check if Elasticsearch is available.
     *
     * @param Client $client
     * @return bool
     */
    private function isElasticsearchAvailable(Client $client): bool
    {
        $ping = null;
        try {
            $ping = $client->ping();
        } catch (ClientResponseException|ServerResponseException) {
            $this->error('Could not connect to Elasticsearch.');
        }

        return !is_null($ping);
    }
    /**
     * Prepare data for bulk insertion.
     */
    private function prepareBulkData(
        string $index,
        string $apiKeyEnv,
        int $timestamp,
        string $label,
        array $location,
        array $measurements): array
    {
        return [
            ['index' => ['_index' => $index]],
            [
                'apiKey' => env($apiKeyEnv),
                'timestamp' => $timestamp,
                'label' => $label,
                'public' => true,
                'location' => $location,
                'measurement' => $measurements,
            ]
        ];
    }

    /**
     * Send bulk request to Elasticsearch.
     */
    private function sendBulkRequest(Client $client, array $params): void
    {
        $client->bulk($params);
    }

    /**
     * Get outdoor measurements.
     */
    private function getOutdoorMeasurements(): array
    {
        return [
            ['label' => 'temperature', 'value' => random_int(15, 42), 'unit' => 'c'],
            ['label' => 'hygrometry', 'value' => random_int(15, 85), 'unit' => '%'],
            ['label' => 'dust', 'value' => random_int(15, 85), 'unit' => 'ug/m3'],
            ['label' => 'pressure', 'value' => random_int(950, 1024), 'unit' => 'hpa'],
            ['label' => 'co', 'value' => random_int(150, 500), 'unit' => 'ppm'],
            ['label' => 'lpg', 'value' => random_int(150, 500), 'unit' => 'ppm'],
            ['label' => 'smoke', 'value' => random_int(150, 500), 'unit' => 'ppm'],
        ];
    }

    /**
     * Get indoor measurements.
     */
    private function getIndoorMeasurements(): array
    {
        return [
            ['label' => 'temperature', 'value' => random_int(15, 42), 'unit' => 'c'],
            ['label' => 'hygrometry', 'value' => random_int(15, 85), 'unit' => '%'],
            ['label' => 'pressure', 'value' => random_int(950, 1024), 'unit' => 'hpa'],
        ];
    }

    /**
     * Generate a random date within a given range.
     *
     * @throws \Exception
     */
    private function randomDateInRange(DateTime $start, DateTime $end): int
    {
        return random_int($start->getTimestamp(), $end->getTimestamp());
    }
}

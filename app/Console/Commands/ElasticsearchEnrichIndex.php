<?php

namespace App\Console\Commands;

use DateTime;
use Elastic\Elasticsearch\Client;
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
        if ($client->ping()) {
            $index = 'air_measurements';

            $this->info('Enrichissement de l\'index');
            $start = new DateTime(date('Y-m-d 00:00:01'));
            $end = new DateTime(date('Y-m-d 23:59:59'));

            $params = [
                'body' => [],
            ];

            for ($i = 1; $i <= 500000; $i++) {
                $params['body'][] = [
                    'index' => [
                        '_index' => $index,
                    ],
                ];
                $date = $this->randomDateInRange($start, $end);

                $params['body'][] = [
                    'apiKey' => env('KEY_EXT'),
                    'timestamp' => $date,
                    'label' => 'Capteur extérieur',
                    'public' => true,
                    'location' => [48.770938, 2.070463],
                    'measurement' => [
                        ['label' => 'temperature',
                            'value' => random_int(15, 42),
                            'unit' => 'c'],
                        ['label' => 'hygrometry',
                            'value' => random_int(15, 85),
                            'unit' => '%'],
                        ['label' => 'dust',
                            'value' => random_int(15, 85),
                            'unit' => 'ug/m3'],
                        ['label' => 'pressure',
                            'value' => random_int(950, 1024),
                            'unit' => 'hpa'],
                        ['label' => 'co',
                            'value' => random_int(150, 500),
                            'unit' => 'ppm'],
                        ['label' => 'lpg',
                            'value' => random_int(150, 500),
                            'unit' => 'ppm'],
                        ['label' => 'smoke',
                            'value' => random_int(150, 500),
                            'unit' => 'ppm'],
                    ],
                ];

                $params['body'][] = [
                    'index' => [
                        '_index' => $index,
                    ],
                ];

                $params['body'][] = [
                    'apiKey' => env('KEY_INT'),
                    'timestamp' => $date,
                    'label' => 'Capteur Maurepas',
                    'public' => true,
                    'location' => [48.770938, 2.050467],
                    'measurement' => [
                        ['label' => 'temperature',
                            'value' => random_int(15, 42),
                            'unit' => 'c'],
                        ['label' => 'hygrometry',
                            'value' => random_int(15, 85),
                            'unit' => '%'],
                        ['label' => 'pressure',
                            'value' => random_int(950, 1024),
                            'unit' => 'hpa'],
                    ],
                ];

                // Every 1000 documents stop and send the bulk request
                if ($i % 1000 == 0) {
                    $responses = $client->bulk($params);

                    // erase the old bulk request
                    $params = ['body' => []];

                    // unset the bulk response when you are done to save memory
                    unset($responses);
                }
            }

            return;
        }

        $this->error('Could not connect to Elasticsearch.');
    }

    /**
     * @throws \Exception
     */
    private function randomDateInRange(DateTime $start, DateTime $end): int
    {
        $randomTimestamp = random_int($start->getTimestamp(), $end->getTimestamp());
        $randomDate = new DateTime();
        $randomDate->setTimestamp($randomTimestamp);

        return $randomTimestamp;
    }
}

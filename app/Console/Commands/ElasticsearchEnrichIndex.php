<?php

namespace App\Console\Commands;

use DateTime;
use Elasticsearch\Client;
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
     * @param Client $client
     * @return void
     * @throws \Exception
     */
    public function handle(Client $client): void
    {
        if ($client->ping()) {

            $index = 'air_measurements';
            $apiKey = 'dKim8c9Vwsdal7JTnqTb5Tv5K2o6IQbKkaZZ9lZAg-s';
            $location = [48.770938, 2.070463];

            $this->info('Enrichissement de l\'index');
            $start = new DateTime('2022-08-01');
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
                $date = $this->randomDateInRange($start, $end);

                $params['body'][] = [
                    'apiKey'            => 'dKim8c9Vwsdal7JTnqTb5Tv5K2o6IQbKkaZZ9lZAg-s',
                    'timestamp'         => $date,
                    'label'         => 'Capteur extérieur',
                    'location'          => [48.770938, 2.070463],
                    'measurement' => [
                        ['label' => 'temperature',
                            'value' => rand(-32, 32),
                            'unit' =>'c'],
                        ['label' => 'hygrometry',
                            'value' => rand(15, 85),
                            'unit' =>'%'],
                        ['label' => 'dust',
                            'value' => rand(15, 85),
                            'unit' =>'ug/m3'],
                        ['label' => 'pressure',
                            'value' => rand(950, 1024),
                            'unit' =>'hpa']
                    ]
                ];
                $params['body'][] = [
                    'index' => [
                        '_index' => $index
                    ]
                ];

                $params['body'][] = [
                    'apiKey'            => '3WV-DnzwSGLE2foAHvMZLMUdWMC8qb0nqzJtJI3n9fc',
                    'timestamp'         => $date,
                    'label'         => 'Capteur bureau',
                    'location'          => [48.770938, 2.020465],
                    'measurement' => [
                        ['label' => 'pressure',
                            'value' => rand(950, 1024),
                            'unit' =>'hpa']
                    ]
                ];

                $params['body'][] = [
                    'index' => [
                        '_index' => $index
                    ]
                ];

                $params['body'][] = [
                    'apiKey'            => 'Y1vMH3cetBhU6Muy5owdzclUAtz1fzloXe4Zq0qqBdM',
                    'timestamp'         => $date,
                    'label'         => 'Capteur Maurepas',
                    'location'          => [48.770938, 2.050467],
                    'measurement' => [
                        ['label' => 'temperature',
                            'value' => rand(-32, 32),
                            'unit' =>'c'],
                        ['label' => 'hygrometry',
                            'value' => rand(15, 85),
                            'unit' =>'%'],
                        ['label' => 'pressure',
                            'value' => rand(950, 1024),
                            'unit' =>'hpa']
                    ]
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

            return;
        }

        $this->error('Could not connect to Elasticsearch.');
    }

    function randomDateInRange(DateTime $start, DateTime $end)
    {
        $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
        $randomDate = new DateTime();
        $randomDate->setTimestamp($randomTimestamp);

        return $randomTimestamp;
    }
}

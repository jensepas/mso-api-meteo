<?php

namespace App\Console\Commands;

use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

class ElasticsearchPing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping Elasticsearch';

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
        if (!is_null($client->ping())) {
            $this->info('pong');

            return;
        }

        $this->error('Could not connect to Elasticsearch.');
    }
}

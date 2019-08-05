<?php

/**
 * Example :
 * http://localhost/Meteo/data *
 *
 * PHP 7.0
 *
 *
 * @location /api/Meteo.php
 */

namespace api;

use lib\Bigbrother as bb;
use Elasticsearch;

class Meteo extends bb
{
    //default return array
    public $return = ["error" => "0", "message" => ""];

    public function data_get($params): array
    {

        if ($this->check($params)) {
            $esclient = Elasticsearch\ClientBuilder::create()
                ->setHosts(["localhost:9200"])
                ->build();

            $paramsRequest = [
                'index' => 'meteo'
            ];
            $paramsRequest['body'] = [
                'size' => 0,
                'aggs' => [
                    'by_libelle' => [
                        'terms' => [
                            'field' => 'libelle'
                        ],
                        'aggs' => [
                            'by_date' => [
                                'date_histogram' => [
                                    'field' => 'date',
                                    'interval' => '120m',
                                    'format' => 'epoch_millis'
                                ],
                                'aggs' => [
                                    'tops' => [
                                        'top_hits' => [
                                            'sort' => [
                                                'date' => [
                                                    'order' => 'asc'
                                                ]
                                            ],
                                            '_source' => [],
                                            'size' => 100
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            if (isset($params['date'])) {
                $end = new \DateTime(date('Y-m-d H:i:s'));

                $paramsRequest['body']['query'] = [
                    'bool' => [
                        'filter' => [
                            'range' => [
                                'date' => [
                                    'gte' => $params['date'],
                                    'lte' => $end->getTimestamp(),
                                    'format' => 'epoch_millis'
                                ]
                            ]
                        ]
                    ]
                ];
            }
            $retour = [];

            foreach ($esclient->search($paramsRequest)['aggregations']['by_libelle']['buckets'] as
                     $hitss) {

                foreach ($hitss['by_date']['buckets'] as $hits) {

                    //print_r($hits);
                    foreach ($hits['tops']['hits']['hits'] as $hit) {

                        $retour[$hit['_source']['libelle']][] = $hit['_source'];
                    }
                }
            }
            $this->return["message"] = $retour;
        } else {
            $this->return["error"] = "ERROR_00025";
            $this->return["message"] = "Empty data, not added";
        }

        return $this->return;
    }

    public function data_post($params = []): array
    {
        if (!empty($params)) {
            $index = 'meteo';
            $date = new \DateTime(date("Y-m-d H:i:s", time()));
            $theDate = $date->getTimestamp();

            $client = Elasticsearch\ClientBuilder::create()
                ->setHosts(["localhost:9200"])
                ->build();

            $apiKey = 'AZERTYU';
            $jsonElastic = [
                'body' => []
            ];

            foreach ($params as $param) {
                $jsonElastic['body'][] = [
                    'index' => [
                        '_index' => $index
                    ]
                ];

                $jsonElastic['body'][] = [
                    'apiKey' => $apiKey,
                    'date' => $theDate,
                    'libelle' => $param['device'],
                    'value' => $param['values'],
                    'unity' => $param['unity']
                ];


            }
            $responses = $client->bulk($jsonElastic);

            $this->return["message"] = "Change OK";

        } else {
            $this->return["error"] = "ERROR_00025";
            $this->return["message"] = "Empty data, not added";
        }

        return $this->return;
    }

    public function data_put($params = []): array
    {
        if (!empty($params)) {

            $this->return["message"] = "Change OK";
        } else {
            $this->return["error"] = "ERROR_00065";
            $this->return["message"] = "Empty search field, not updated";
        }

        return $this->return;
    }

    public function data_delete($params = []): array
    {
        if (!empty($params)) {

            $this->return["message"] = "Removed OK";
        } else {
            $this->return["error"] = "ERROR_00065";
            $this->return["message"] = "Empty search field, not removed";
        }

        return $this->return;
    }
}
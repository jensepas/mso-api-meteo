<?php

/**
 * Example :
 * http://localhost/Meteo/data *
 *
 * PHP 7.0
 *
 *
 * @location /api/meteo.php
 */

namespace api;

use lib\BigBrother;
use Elasticsearch;

class Meteo extends BigBrother
{
    //default return array
    public $return = ["error" => "0", "message" => ""];

    /**
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function data_get($params): array
    {
        if ($this->check($params)) {
            //if ($this->checkApiKey($params)) {


            $esClient = Elasticsearch\ClientBuilder::create()
                ->setHosts([ES_ADRESS])
                ->build();

            $paramsRequest['index'] = ES_INDEX;

            if (!empty($params['apikey'])) {
                $paramsRequest['body']['query']['bool']['must'][]['term']['apiKey'] = $params['apikey'];
            }
            //$paramsRequest['body']['aggs']['sale_date']['date_histogram']['field'] = 'date';
            //$paramsRequest['body']['aggs']['sale_date']['date_histogram']['interval'] = 100;
            //$paramsRequest['body']['aggs']['sale_date']['histogram']['minimum_interval'] = "minute";
            //$paramsRequest['body']['aggs']['sale_date']['histogram']['format'] = 'epoch_millis';

            if (!empty($params['sensor'])) {
                $paramsRequest['body']['query']['bool']['must'][]['term']['measurement.label'] = $params['sensor'];
            }

            $end = new \DateTime(date('Y-m-d H:i:s'));
            $endDate = $end->getTimestamp();

            if (!empty($params['end'])) {
                $endDate = $params['end'];
            }

            $paramsRequest['body']['query']['bool']['filter']['range']['timestamp']['lte'] = $endDate;

            if (!empty($params['start'])) {
                $paramsRequest['body']['query']['bool']['filter']['range']['timestamp']['gte'] = $params['start'];
            }

            $paramsRequest['body']['sort']['timestamp']['order'] = 'desc';
            $size = !empty($params['size']) ? $params['size'] : 10000;
            $paramsRequest['body']['size'] = $size;

            $return = [];
            $esReturn = $esClient->search($paramsRequest);

            foreach ($esReturn['hits']['hits'] as $hit) {
                $return[] = $hit['_source'];
            }

            usort($return, function ($a1, $a2) {
                return $a1['timestamp'] - $a2['timestamp'];
            });

            $this->return["message"] = $return;
            //  } else {
            //     $this->return["error"] = "ERROR_00015";
            //     $this->return["message"] = "Invalid API key";
            // }

        } else {
            $this->return["error"] = "ERROR_00025";
            $this->return["message"] = "Empty data, not added";
        }
        return $this->return;
    }

    /**
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function data_post($params = []): array
    {
        if (!empty($params)) {

            if ($this->checkApiKey($params[0]['apikey'])) {
                $index = ES_INDEX;

                $date = new \DateTime(date("Y-m-d H:i:s", time()));
                $theDate = $date->getTimestamp();

                $esClient = Elasticsearch\ClientBuilder::create()
                    ->setHosts([ES_ADRESS])
                    ->build();

                $apiKey = $params[0]['apikey'];
                $location = $params[1]['location'];
                $jsonElastic = [
                    'body' => []
                ];

                foreach ($params[2]['sensors'] as $param) {
                    $jsonElastic['body'][] = [
                        'index' => [
                            '_index' => $index
                        ]
                    ];

                    $jsonElastic['body'][] = [
                        'apiKey' => $apiKey,
                        'location' => $location,
                        'timestamp' => $theDate,
                        'measurement.label' => $param['device'],
                        'measurement.value' => $param['values'],
                        'measurement.unit' => $param['unity']
                    ];
                }
                $responses = $esClient->bulk($jsonElastic);

                if ($responses['errors']) {
                    $message = "Indexation ko";
                    $this->return["error"] = "ERROR_00500";
                } else {
                    $message = "Indexation OK";
                }

                $this->return["message"] = $message;

            } else {
                $this->return["error"] = "ERROR_00015";
                $this->return["message"] = "Invalid API key";
            }
        } else {
            $this->return["error"] = "ERROR_00025";
            $this->return["message"] = "Empty data, not added";
        }

        return $this->return;
    }

    /**
     * @param array $params
     * @return array
     */
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

    /**
     * @param array $params
     * @return array
     */
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
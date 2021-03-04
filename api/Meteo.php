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

use DateTime;
use Elasticsearch;
use Exception;
use lib\BigBrother;

class Meteo extends BigBrother
{
    //default return array
    public $return = ["error" => "0", "count" => "0", "message" => ""];

    /**
     * @param $params
     * @return array
     * @throws Exception
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
                $paramsRequest['body']['query']['bool']['must'][]['match']['apiKey'] = $params['apikey'];
            }
            //$paramsRequest['body']['aggs']['sale_date']['date_histogram']['field'] = 'date';
            //$paramsRequest['body']['aggs']['sale_date']['date_histogram']['interval'] = 100;
            //$paramsRequest['body']['aggs']['sale_date']['histogram']['minimum_interval'] = "minute";
            //$paramsRequest['body']['aggs']['sale_date']['histogram']['format'] = 'epoch_millis';

            if (!empty($params['label'])) {
                $paramsRequest['body']['query']['bool']['must'][]['match']['label'] = $params['label'];
            }

            if (!empty($params['sensor'])) {
                $paramsRequest['body']['query']['bool']['must'][]['match']['measurement.label'] = $params['sensor'];
            }

            if (!empty($params['location'])) {
                $location = explode(',', $params['location']);

                if (!isset($paramsRequest['body']['query']['bool']['must'])) {
                    $paramsRequest['body']['query']['bool']['must'][]['match_all'] = (object)[];
                }
                $locationTopLeft = ['lat' => $location[0], 'lon' => $location[1]];
                $locationBottonRight = ['lat' => $location[2], 'lon' => $location[3]];
                $paramsRequest['body']['query']['bool']['filter'][]['geo_bounding_box']['location'] =
                    ['top_left' => $locationTopLeft, 'bottom_right' => $locationBottonRight];

                $paramsRequest['body']['aggs']['map_bounds']['geohash_grid']['field'] = 'location';
                $paramsRequest['body']['aggs']['map_bounds']['geohash_grid']['precision'] = '50m';
                $paramsRequest['body']['aggs']['map_bounds']['geohash_grid']['size'] = 6000;

                $paramsRequest['body']['aggs']['map_bounds']['aggs']['cell']['geo_bounds']['field'] = 'location';
                $paramsRequest['body']['aggs']['map_bounds']['aggs']['by_top_hit']['top_hits']['size'] = 1;
                $paramsRequest['body']['aggs']['map_bounds']['aggs']['by_top_hit']['top_hits']['sort']['timestamp']['order'] = 'desc';
            }

            $end = new DateTime(date('Y-m-d H:i:s'));
            $endDate = $end->getTimestamp();

            if (!empty($params['end'])) {
                $endDate = $params['end'];
            }
            $paramsRequest['body']['query']['bool']['filter'][]['range']['timestamp']['lte'] = $endDate;

            if (!empty($params['start'])) {
                $paramsRequest['body']['query']['bool']['filter'][]['range']['timestamp']['gte'] = $params['start'];
            }

            $paramsRequest['body']['sort']['timestamp']['order'] = 'desc';
            $size = isset($params['size']) && $params['size'] !== '' ? $params['size'] : 100;
            $paramsRequest['body']['size'] = $size;


            $return = [];
            $esReturn = $esClient->search($paramsRequest);

            if (!empty($params['maps'])) {
                foreach ($esReturn['aggregations']['map_bounds']['buckets'] as $hit) {
                    $return[] = $hit['by_top_hit']['hits']['hits'][0]['_source'];
                }

            } else {
                foreach ($esReturn['hits']['hits'] as $hit) {
                    $return[] = $hit['_source'];
                }

                usort($return, function ($a1, $a2) {
                    return $a1['timestamp'] - $a2['timestamp'];
                });
            }

            $this->return["message"] = $return;
            $this->return["count"] = count($return);

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
     * @throws Exception
     */
    public function data_post($params = []): array
    {
        if (!empty($params)) {

            if ($row = $this->checkApiKey($params[0]['apikey'])) {
                $index = ES_INDEX;

                $date = new DateTime(date("Y-m-d H:i:s", time()));
                $theDate = $date->getTimestamp();

                $esClient = Elasticsearch\ClientBuilder::create()
                    ->setHosts([ES_ADRESS])
                    ->build();

                $apiKey = $params[0]['apikey'];
                $location = $params[1]['location'];
                $measurements = [];
                $jsonElastic = [
                    'body' => []
                ];

                $jsonElastic['body'][] = [
                    'index' => [
                        '_index' => $index
                    ]
                ];
                foreach ($params[2]['sensors'] as $param) {
                    $measurements[] = [
                        'label' => $param['device'],
                        'value' => $param['values'],
                        'unit' => $param['unity']
                    ];
                }

                $jsonElastic['body'][] = [
                    'apiKey' => $apiKey,
                    'location' => $location,
                    'timestamp' => $theDate,
                    'label' => $row['label'],
                    'measurement' => $measurements
                ];

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
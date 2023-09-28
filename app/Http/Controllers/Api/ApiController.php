<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use DateTime;
use Elastic\Elasticsearch\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *

     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(['message' => '', 'count' => '']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Client $client
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Client $client, Request $request): JsonResponse
    {
        $params = json_decode($request->getContent(), true);
        $return =  [];
        $return["error"] = "ERROR_00025";
        $return["message"] = "Empty data, not added";

        if (!empty($params)) {
            $apiKey = $params[0]['apikey'];
            $row = DB::table('sensor')
                ->select('label', 'is_published')
                ->selectRaw('ST_X(coordinates) as longitude')
                ->selectRaw('ST_Y(coordinates) as latitude')
                ->where('token', $apiKey)->first();
            if (!empty($row)) {
                $index =  env('ELASTICSEARCH_INDEX');

                $date = new DateTime(date("Y-m-d H:i:s", time()));
                $theDate = $date->getTimestamp();

                $location = [$row->longitude, $row->latitude];

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
                    'public' => $row->is_published,
                    'timestamp' => $theDate,
                    'label' => $row->label,
                    'measurement' => $measurements
                ];

                $responses = $client->bulk($jsonElastic);

                $message = "Indexation OK";
                if ($responses['errors']) {
                    $message = "Indexation ko";
                    $return["error"] = "ERROR_00500";
                }

                $return["message"] = $message;
            } else {
                $return["error"] = "ERROR_00015";
                $return["message"] = "Invalid API key";
            }
        }

        return response()->json($return);
    }

    /**
     * Display the specified resource.
     *
     * @param Client $client
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function show(Client $client, Request $request): JsonResponse
    {
        $paramsRequest =  [];
        $paramsRequest['index'] = env('ELASTICSEARCH_INDEX');

        if (!empty($request->apikey)) {
            $paramsRequest['body']['query']['bool']['must'][]['match']['apiKey'] = $request->apikey;
        }

        if (!empty($request->location)) {
            $location = explode(',', $request->location);

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
            $paramsRequest['body']['aggs']['map_bounds']['aggs']['by_top_hit']['top_hits']['sort']['timestamp']['order']
                = 'desc';
        }

        $end = new DateTime(date('Y-m-d H:i:s'));
        $endDate = $end->getTimestamp();

        if (!empty($request->end)) {
            $endDate = $request->end;
        }
        $paramsRequest['body']['query']['bool']['filter'][]['range']['timestamp']['lte'] = $endDate;

        if (!empty($request->start)) {
            $paramsRequest['body']['query']['bool']['filter'][]['range']['timestamp']['gte'] = $request->start;
        }

        $paramsRequest['body']['query']['bool']['must'][]['match']['public'] = true;
        $paramsRequest['body']['sort']['timestamp']['order'] = 'desc';
        $size = isset($request->size) && $request->size !== '' ? $request->size : 100;
        $paramsRequest['body']['size'] = $size;

        $return = [];
        $esReturn = $client->search($paramsRequest);


        if (!empty($request->maps)) {
            foreach ($esReturn['aggregations']['map_bounds']['buckets'] as $hit) {
                $return[] = $hit['by_top_hit']['hits']['hits'][0]['_source'];
            }
        } else {
            foreach ($esReturn['hits']['hits'] as $hit) {
                $return[] = $hit['_source'];
            }

            usort($return, function ($val1, $val2) {
                return $val1['timestamp'] - $val2['timestamp'];
            });
        }

        return response()->json([
            'message' => $return,
            'count' => count($return)
        ]);
    }
}

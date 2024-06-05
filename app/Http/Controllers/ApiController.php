<?php

namespace App\Http\Controllers;

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
     */
    public function index(): JsonResponse
    {
        return response()->json(['message' => '', 'count' => '']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws \Exception
     */
    public function store(Client $client, Request $request): JsonResponse
    {

        $params = json_decode($request->getContent(), true);
        $return = [];
        $return['error'] = 'ERROR_00025';
        $return['message'] = 'Empty data, not added';

        if (! empty($params)) {
            $apiKey = $params[0]['apikey'];

            $row = DB::table('sensor')
                ->select('label', 'is_published')
                ->selectRaw('ST_X(coordinates) as longitude')
                ->selectRaw('ST_Y(coordinates) as latitude')
                ->where('token', $apiKey)->first();

            if (! empty($row)) {
                $index = env('ELASTICSEARCH_INDEX');

                $date = new DateTime(date('Y-m-d H:i:s', time()));
                $theDate = $date->getTimestamp();

                $location = [$row->longitude, $row->latitude];

                $measurements = [];
                $jsonElastic = [
                    'body' => [],
                ];

                $jsonElastic['body'][] = [
                    'index' => [
                        '_index' => $index,
                    ],
                ];
                foreach ($params[2]['sensors'] as $param) {
                    $measurements[] = [
                        'label' => $param['device'],
                        'value' => $param['values'],
                        'unit' => $param['unity'],
                    ];
                }

                $jsonElastic['body'][] = [
                    'apiKey' => $apiKey,
                    'location' => $location,
                    'public' => $row->is_published,
                    'timestamp' => $theDate,
                    'label' => $row->label,
                    'measurement' => $measurements,
                ];

                $responses = $client->bulk($jsonElastic);

                $message = 'Indexation OK';
                if ($responses['errors']) {
                    $message = 'Indexation ko';
                    $return['error'] = 'ERROR_00500';
                }

                $return['message'] = $message;
            } else {
                $return['error'] = 'ERROR_00015';
                $return['message'] = 'Invalid API key';
            }
        }

        return response()->json($return);
    }

    /**
     * Display the specified resource.
     *
     * @throws \Exception
     */
    public function show(Client $client, Request $request): JsonResponse
    {

        $paramsRequest = $this->initializeParamsRequest();

        $this->addApiKeyFilter($paramsRequest, $request->apikey);
        $this->addLocationFilter($paramsRequest, $request->location);
        $this->addDateRangeFilter($paramsRequest, $request->start, $request->end);

        $this->addPublicFilter($paramsRequest);
        $this->addSortAndSize($paramsRequest, $request->size);

        $esReturn = $client->search($paramsRequest);
        $return = $this->prepareResponse($esReturn, $request->maps);

        return response()->json([
            'message' => $return,
            'count' => count($return),
        ]);
    }

    private function initializeParamsRequest(): array
    {
        return [
            'index' => env('ELASTICSEARCH_INDEX'),
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [],
                        'filter' => [],
                    ],
                ],
            ],
        ];
    }

    private function addApiKeyFilter(array &$paramsRequest, $apiKey): void
    {
        if (! empty($apiKey)) {
            $paramsRequest['body']['query']['bool']['must'][]['match']['apiKey'] = $apiKey;
        }
    }

    private function addLocationFilter(array &$paramsRequest, $location): void
    {
        if (! empty($location)) {
            $locationParts = explode(',', $location);
            $locationTopLeft = ['lat' => $locationParts[0], 'lon' => $locationParts[1]];
            $locationBottomRight = ['lat' => $locationParts[2], 'lon' => $locationParts[3]];

            if (empty($paramsRequest['body']['query']['bool']['must'])) {
                $paramsRequest['body']['query']['bool']['must'][]['match_all'] = (object) [];
            }

            $paramsRequest['body']['query']['bool']['filter'][]['geo_bounding_box']['location'] = [
                'top_left' => $locationTopLeft,
                'bottom_right' => $locationBottomRight,
            ];

            $this->addAggregations($paramsRequest);
        }
    }

    private function addAggregations(array &$paramsRequest): void
    {
        $paramsRequest['body']['aggs']['map_bounds']['geohash_grid'] = [
            'field' => 'location',
            'precision' => '50m',
            'size' => 6000,
        ];

        $paramsRequest['body']['aggs']['map_bounds']['aggs']['cell']['geo_bounds']['field'] = 'location';
        $paramsRequest['body']['aggs']['map_bounds']['aggs']['by_top_hit']['top_hits'] = [
            'size' => 1,
            'sort' => [
                'timestamp' => [
                    'order' => 'desc',
                ],
            ],
        ];
    }

    private function addDateRangeFilter(array &$paramsRequest, $start, $end): void
    {
        $endDate = $end ? $end : (new DateTime())->getTimestamp();

        $paramsRequest['body']['query']['bool']['filter'][]['range']['timestamp']['lte'] = $endDate;

        if (! empty($start)) {
            $paramsRequest['body']['query']['bool']['filter'][]['range']['timestamp']['gte'] = $start;
        }
    }

    private function addPublicFilter(array &$paramsRequest): void
    {
        $paramsRequest['body']['query']['bool']['must'][]['match']['public'] = true;
    }

    private function addSortAndSize(array &$paramsRequest, $size): void
    {
        $paramsRequest['body']['sort']['timestamp']['order'] = 'desc';
        $paramsRequest['body']['size'] = $size ?? 100;
    }

    private function prepareResponse($esReturn, $maps): array
    {
        $return = [];

        if (! empty($maps)) {
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

        return $return;
    }
}

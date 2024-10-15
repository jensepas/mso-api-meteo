<?php

use App\Http\Controllers\ApiController;
use Elastic\Elasticsearch\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class ApiControllerTest extends TestCase
{
    protected $client;
    protected $request;
    protected $apiController;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the ElasticSearch Client and the Request
        $this->client = $this->createMock(Client::class);
        $this->request = $this->createMock(Request::class);

        // Initialize the controller
        $this->apiController = new ApiController();
    }

    public function testStoreWithEmptyParams()
    {
        $this->request->method('getContent')->willReturn(json_encode([]));

        // Call the store method
        $response = $this->apiController->store($this->client, $this->request);

        // Assert the response
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);

        $this->assertEquals('ERROR_00025', $data['error']);
        $this->assertEquals('Empty data, not added', $data['message']);
    }

    public function testStoreWithInvalidApiKey()
    {
        $params = json_encode([['apikey' => 'invalid_key']]);
        $this->request->method('getContent')->willReturn($params);

        // Mock the DB::table() result for invalid API key
        DB::shouldReceive('table')
            ->with('sensor')
            ->andReturnSelf();
        DB::shouldReceive('select')
            ->andReturnSelf();
        DB::shouldReceive('selectRaw')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('token', 'invalid_key')
            ->andReturnSelf();
        DB::shouldReceive('first')
            ->andReturn(null); // No row found for the API key

        // Call the store method
        $response = $this->apiController->store($this->client, $this->request);

        // Assert the response
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);

        $this->assertEquals('ERROR_00015', $data['error']);
        $this->assertEquals('Invalid API key', $data['message']);
    }

    public function testStoreWithValidData()
    {
        $params = json_encode([
            ['apikey' => 'valid_key'],
            [],
            ['sensors' => [['device' => 'sensor1', 'values' => 10, 'unity' => 'unit1']]]
        ]);
        $this->request->method('getContent')->willReturn($params);

        // Mock the DB::table() result for valid API key
        DB::shouldReceive('table')
            ->with('sensor')
            ->andReturnSelf();
        DB::shouldReceive('select')
            ->andReturnSelf();
        DB::shouldReceive('selectRaw')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('token', 'valid_key')
            ->andReturnSelf();
        DB::shouldReceive('first')
            ->andReturn((object) [
                'label' => 'test_label',
                'is_published' => true,
                'longitude' => 50.0,
                'latitude' => 40.0,
            ]);

        // Mock the Elasticsearch client bulk method
        $this->client->expects($this->once())
            ->method('bulk')
            ->willReturn(['errors' => false]);

        // Call the store method
        $response = $this->apiController->store($this->client, $this->request);

        // Assert the response
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);

        $this->assertEquals('Indexation OK', $data['message']);
        $this->assertArrayNotHasKey('error', $data);
    }

    public function testShowWithValidResponse()
    {
        $this->request->apikey = 'valid_key';
        $this->request->location = '40.0,50.0,41.0,51.0';
        $this->request->start = null;
        $this->request->end = null;
        $this->request->size = 100;
        $this->request->maps = false;

        // Mock Elasticsearch search method
        $esReturn = [
            'hits' => [
                'hits' => [
                    ['_source' => ['timestamp' => 123456789, 'data' => 'test_data']]
                ]
            ]
        ];

        $this->client->expects($this->once())
            ->method('search')
            ->willReturn($esReturn);

        // Call the show method
        $response = $this->apiController->show($this->client, $this->request);

        // Assert the response
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);

        $this->assertCount(1, $data['message']);
        $this->assertEquals(1, $data['count']);
    }
}

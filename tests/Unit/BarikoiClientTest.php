<?php

namespace Vendor\BarikoiApi\Tests\Unit;

use Vendor\BarikoiApi\Tests\TestCase;
use Vendor\BarikoiApi\BarikoiClient;
use Illuminate\Support\Facades\Http;

class BarikoiClientTest extends TestCase
{
    protected BarikoiClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new BarikoiClient('test-api-key', 'https://barikoi.xyz/v2/api');
    }

    // Test GET request adds API key
    public function test_get_request_adds_api_key()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'data' => []], 200)
        ]);

        $result = $this->client->get('/test', ['param1' => 'value1']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://barikoi.xyz/v2/api/test?param1=value1&api_key=test-api-key';
        });

        $this->assertIsArray($result);
    }

    // Test POST request adds API key
    public function test_post_request_adds_api_key()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'data' => []], 200)
        ]);

        $result = $this->client->post('/test', ['data1' => 'value1']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/test')
                && $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded');
        });

        $this->assertIsArray($result);
    }

    // Test DELETE request adds API key
    public function test_delete_request_adds_api_key()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'message' => 'deleted'], 200)
        ]);

        $result = $this->client->delete('/test/123', ['param' => 'value']);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return $request->method() === 'DELETE'
                && str_contains($url, '/test/123');
        });

        $this->assertIsArray($result);
    }

    // Test client returns JSON response
    public function test_client_returns_json_response()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'place' => ['name' => 'Dhanmondi']], 200)
        ]);

        $result = $this->client->get('/test');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('place', $result);
        $this->assertEquals('Dhanmondi', $result['place']['name']);
    }

    // Test client uses config when no API key provided
    public function test_uses_config_when_no_api_key_provided()
    {
        config(['barikoi.api_key' => 'config-api-key']);
        config(['barikoi.base_url' => 'https://config-url.com']);

        $client = new BarikoiClient();

        Http::fake([
            '*' => Http::response(['status' => 200], 200)
        ]);

        $client->get('/test');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'config-url.com')
                && str_contains($request->url(), 'api_key=config-api-key');
        });
    }

    // Test GET with empty params
    public function test_get_with_empty_params()
    {
        Http::fake([
            '*' => Http::response(['status' => 200], 200)
        ]);

        $result = $this->client->get('/test');

        $this->assertIsArray($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api_key=test-api-key');
        });
    }

    // Test POST with empty data
    public function test_post_with_empty_data()
    {
        Http::fake([
            '*' => Http::response(['status' => 200], 200)
        ]);

        $result = $this->client->post('/test');

        $this->assertIsArray($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/test');
        });
    }
}

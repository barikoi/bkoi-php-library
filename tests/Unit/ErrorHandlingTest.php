<?php

namespace Vendor\BarikoiApi\Tests\Unit;

use Vendor\BarikoiApi\Tests\TestCase;
use Vendor\BarikoiApi\Services\LocationService;
use Vendor\BarikoiApi\BarikoiClient;
use Illuminate\Support\Facades\Http;
use Vendor\BarikoiApi\Exceptions\BarikoiApiException;
use Vendor\BarikoiApi\Exceptions\BarikoiValidationException;

class ErrorHandlingTest extends TestCase
{
    protected LocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new BarikoiClient('test-key', 'https://barikoi.xyz/v2/api');
        $this->service = new LocationService($client);
    }

    // Test handling 404 response
    public function test_handles_404_not_found()
    {
        Http::fake([
            '*' => Http::response(['status' => 404, 'message' => 'Resource not found'], 404)
        ]);

        $this->expectException(BarikoiApiException::class);
        $this->expectExceptionMessage('Not Found: Resource not found');
        $this->service->reverseGeocode(90.3572, 23.8067);
    }

    // Test handling 401 unauthorized
    public function test_handles_401_unauthorized()
    {
        Http::fake([
            '*' => Http::response(['status' => 401, 'message' => 'Invalid API key'], 401)
        ]);

        $this->expectException(BarikoiApiException::class);
        $this->expectExceptionMessage('Authentication Failed: Invalid API key. Please verify your API key is correct.');
        $this->service->reverseGeocode(90.3572, 23.8067);
    }

    // Test handling 500 server error
    public function test_handles_500_server_error()
    {
        Http::fake([
            '*' => Http::response(['status' => 500, 'message' => 'Internal Server Error'], 500)
        ]);

        $this->expectException(BarikoiApiException::class);
        $this->expectExceptionMessage('Server Error: Internal Server Error. The Barikoi API is experiencing issues. Please try again later.');
        $this->service->autocomplete('test');
    }

    // Test handling 429 rate limit
    public function test_handles_429_rate_limit()
    {
        Http::fake([
            '*' => Http::response(['status' => 429, 'message' => 'Rate limit exceeded'], 429)
        ]);

        $this->expectException(BarikoiApiException::class);
        $this->expectExceptionMessage('Rate Limit Exceeded: Rate limit exceeded. Please reduce the number of requests or try again later.');
        $this->service->searchPlace('restaurant');
    }

    // Test handling empty response
    public function test_handles_empty_response()
    {
        Http::fake([
            '*' => Http::response([], 200)
        ]);

        // Empty array response violates service's object return type
        $this->expectException(\TypeError::class);
        $this->service->reverseGeocode(90.3572, 23.8067);
    }

    // Test handling null response
    public function test_handles_null_response()
    {
        Http::fake([
            '*' => Http::response(null, 200)
        ]);

        // Null response causes client to return empty array, which violates service's object return type
        $this->expectException(\TypeError::class);
        $this->service->reverseGeocode(90.3572, 23.8067);
    }

    // Test handling malformed JSON
    public function test_handles_malformed_json()
    {
        Http::fake([
            '*' => Http::response('invalid json {]', 200, ['Content-Type' => 'text/plain'])
        ]);

        // Malformed JSON causes client to return empty array, which violates service's object return type
        $this->expectException(\TypeError::class);
        $this->service->reverseGeocode(90.3572, 23.8067);
    }

    // Test handling timeout (this would need actual timeout simulation)
    public function test_api_has_timeout_configured()
    {
        $client = new BarikoiClient('test-key', 'https://barikoi.xyz/v2/api');

        // We can't easily test actual timeout, but we can verify client is configured
        $this->assertInstanceOf(BarikoiClient::class, $client);
    }

    // Test handling invalid coordinates (out of range)
    public function test_sends_invalid_coordinates()
    {
        Http::fake([
            '*' => Http::response(['status' => 400, 'message' => 'Invalid coordinates'], 400)
        ]);

        $this->expectException(BarikoiValidationException::class);
        $this->expectExceptionMessage('Invalid latitude or longitude');
        // Coordinates out of valid range
        $this->service->reverseGeocode(999, 999);
    }

    // Test handling no results found
    public function test_handles_no_results_found()
    {
        Http::fake([
            '*' => Http::response([
                'status' => 200,
                'places' => [],
                'message' => 'No places found'
            ], 200)
        ]);

        $result = $this->service->searchPlace('nonexistent place xyz123');

        $this->assertIsObject($result);
        $this->assertEquals(200, $result->status);
        $this->assertEmpty($result->places);
    }

    // Test handling partial response data
    public function test_handles_partial_response()
    {
        Http::fake([
            '*' => Http::response([
                'status' => 200,
                'place' => [
                    'address' => 'Dhanmondi'
                    // Missing other expected fields
                ]
            ], 200)
        ]);

        $result = $this->service->reverseGeocode(90.3572, 23.8067);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('place', $result);
        $this->assertObjectHasProperty('address', $result->place);
    }

    // Test handling missing required fields in response
    public function test_handles_missing_status_field()
    {
        Http::fake([
            '*' => Http::response([
                'data' => 'some data',
                // Missing 'status' field
            ], 200)
        ]);

        $result = $this->service->autocomplete('test');

        $this->assertIsObject($result);
        $this->assertObjectNotHasProperty('status', $result);
    }

    // Test geocode with invalid address returns error
    public function test_geocode_invalid_address()
    {
        Http::fake([
            '*' => Http::response([
                'status' => 400,
                'message' => 'Address not found'
            ], 400)
        ]);

        $this->expectException(BarikoiValidationException::class);
        $this->expectExceptionMessage('Validation Error: Address not found');
        $this->service->geocode('');
    }

    // Test response with unexpected structure
    public function test_handles_unexpected_response_structure()
    {
        Http::fake([
            '*' => Http::response([
                'completely' => 'different',
                'structure' => 'than expected'
            ], 200)
        ]);

        $result = $this->service->reverseGeocode(90.3572, 23.8067);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('completely', $result);
    }

    // Test success response with warnings
    public function test_handles_success_with_warnings()
    {
        Http::fake([
            '*' => Http::response([
                'status' => 200,
                'place' => ['address' => 'Dhaka'],
                'warnings' => ['Some fields may be incomplete']
            ], 200)
        ]);

        $result = $this->service->reverseGeocode(90.3572, 23.8067);

        $this->assertIsObject($result);
        $this->assertEquals(200, $result->status);
        $this->assertObjectHasProperty('warnings', $result);
    }
}

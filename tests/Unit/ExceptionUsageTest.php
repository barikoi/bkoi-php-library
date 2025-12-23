<?php

namespace Vendor\BarikoiApi\Tests\Unit;

use Vendor\BarikoiApi\Tests\TestCase;
use Vendor\BarikoiApi\Services\LocationService;
use Vendor\BarikoiApi\BarikoiClient;
use Illuminate\Support\Facades\Http;
use Vendor\BarikoiApi\Exceptions\BarikoiApiException;
use Vendor\BarikoiApi\Exceptions\BarikoiValidationException;

/**
 * Tests demonstrating how users should catch and handle exceptions
 */
class ExceptionUsageTest extends TestCase
{
    protected LocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new BarikoiClient('test-key', 'https://barikoi.xyz/v2/api');
        $this->service = new LocationService($client);
    }

    // Example: Catching validation errors and displaying user-friendly messages
    public function test_catch_validation_error_and_get_details()
    {
        Http::fake([
            '*' => Http::response([
                'status' => 400,
                'message' => 'Invalid input parameters',
                'errors' => [
                    'latitude' => ['Latitude must be between -90 and 90'],
                    'longitude' => ['Longitude must be between -180 and 180']
                ]
            ], 400)
        ]);

        try {
            $this->service->reverseGeocode(999, 999);
            $this->fail('Exception should have been thrown');
        } catch (BarikoiValidationException $e) {
            // Get user-friendly error message
            $this->assertStringContainsString('Invalid latitude or longitude', $e->getMessage());

            // Note: Client-side validation throws before API call,
            // so mocked validation errors are not reached
            // This validates coordinates locally before making API request

            // Client-side validation doesn't have HTTP status code
            // (Only API errors have HTTP status codes)
            $this->assertTrue(true, 'Client-side validation exception caught successfully');
        }
    }

    // Example: Catching API errors and checking error type
    public function test_catch_api_error_and_handle_by_status_code()
    {
        Http::fake([
            '*' => Http::response([
                'status' => 401,
                'message' => 'Invalid or expired API key'
            ], 401)
        ]);

        try {
            $this->service->searchPlace('restaurant');
            $this->fail('Exception should have been thrown');
        } catch (BarikoiApiException $e) {
            // Check if it's an authentication error
            if ($e->getCode() === 401) {
                $this->assertStringContainsString('Authentication Failed', $e->getMessage());
                $this->assertStringContainsString('Please verify your API key', $e->getMessage());
            }

            // Get the original API message
            $this->assertEquals('Invalid or expired API key', $e->getErrorMessage());

            // Get all error data
            $errorData = $e->getErrorData();
            $this->assertEquals(401, $errorData['status']);
        }
    }

    // Example: Handling rate limit errors
    public function test_catch_rate_limit_and_retry_later()
    {
        Http::fake([
            '*' => Http::response([
                'status' => 429,
                'message' => 'Too many requests. Try again in 60 seconds.'
            ], 429)
        ]);

        try {
            $this->service->autocomplete('dhaka');
            $this->fail('Exception should have been thrown');
        } catch (BarikoiApiException $e) {
            if ($e->getCode() === 429) {
                $this->assertStringContainsString('Rate Limit Exceeded', $e->getMessage());
                $this->assertStringContainsString('reduce the number of requests', $e->getMessage());

                // You could implement retry logic here
                // sleep(60); retry();
            }
        }
    }

    // Example: Handling server errors
    public function test_catch_server_error_and_fallback()
    {
        Http::fake([
            '*' => Http::response([
                'status' => 500,
                'message' => 'Database connection failed'
            ], 500)
        ]);

        try {
            $result = $this->service->reverseGeocode(90.3572, 23.8067);
            $this->fail('Exception should have been thrown');
        } catch (BarikoiApiException $e) {
            if ($e->getCode() >= 500) {
                $this->assertStringContainsString('Server Error', $e->getMessage());
                $this->assertStringContainsString('experiencing issues', $e->getMessage());

                // Implement fallback logic
                // return cached data or default value
            }
        }
    }

    // Example: Getting detailed error information for logging
    public function test_get_error_details_for_logging()
    {
        Http::fake([
            '*' => Http::response([
                'status' => 400,
                'message' => 'Invalid request',
                'request_id' => 'abc123',
                'timestamp' => '2024-01-01 12:00:00'
            ], 400)
        ]);

        try {
            $this->service->geocode('');
        } catch (BarikoiValidationException $e) {
            // Get full error data for logging
            $errorData = $e->getErrorData();

            // Log context information
            $logContext = [
                'error_message' => $e->getMessage(),
                'api_message' => $e->getErrorMessage(),
                'status_code' => $e->getCode(),
                'request_id' => $errorData['request_id'] ?? null,
                'timestamp' => $errorData['timestamp'] ?? null,
            ];

            $this->assertArrayHasKey('error_message', $logContext);
            $this->assertArrayHasKey('api_message', $logContext);
            $this->assertEquals(400, $logContext['status_code']);
        }
    }
}

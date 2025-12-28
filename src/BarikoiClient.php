<?php

namespace Barikoi\BarikoiApis;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Barikoi\BarikoiApis\Exceptions\BarikoiApiException;
use Barikoi\BarikoiApis\Exceptions\BarikoiValidationException;

/**
 * Barikoi HTTP Client - Handles all API requests
 */
class BarikoiClient
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey ?? config('barikoi.api_key');
        $this->baseUrl = $baseUrl ?? config('barikoi.base_url');
    }

    // Setup HTTP client with base URL and timeout
    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout(60)
            ->withHeaders(['Accept' => 'application/json']);
    }

    /**
     * Handle API response and throw appropriate exceptions for errors
     * Returns object (stdClass) matching Barikoi API response format
     */
    protected function handleResponse(Response $response): array|object
    {
        if ($response->successful()) {
            $json = $response->json();
            if ($json === null) {
                return (object) [];
            }

            // Convert to object using json_decode with false flag
            // This keeps arrays as arrays (matching Barikoi API format) while converting objects to stdClass
            return json_decode($response->body(), false);
        }

        // Handle validation errors (400 Bad Request)
        if ($response->status() === 400) {
            throw BarikoiValidationException::fromResponse($response);
        }

        // Handle all other API errors
        throw BarikoiApiException::fromResponse($response);
    }

    // GET request - for fetching data
    public function get(string $endpoint, array $params = []): array|object
    {
        $params['api_key'] = $this->apiKey;
        $response = $this->client()->get($endpoint, $params);
        return $this->handleResponse($response);
    }

    // POST request - for sending data (form-encoded)
    public function post(string $endpoint, array $data = []): array|object
    {
        $data['api_key'] = $this->apiKey;
        $response = $this->client()->asForm()->post($endpoint, $data);

        return $this->handleResponse($response);
    }

    // POST request with JSON body (api_key in query string)
    public function postJson(string $endpoint, array $data = []): array|object
    {
        // api_key is in query string for JSON endpoints like /routing
        $separator = str_contains($endpoint, '?') ? '&' : '?';
        $endpointWithKey = $endpoint . $separator . 'key=' . $this->apiKey;
        $response = $this->client()->post($endpointWithKey, $data);

        return $this->handleResponse($response);
    }

    // POST request with JSON body (api_key in body)
    public function postJsonWithKeyInBody(string $endpoint, array $data = []): array|object
    {
        // api_key is in the JSON body for endpoints like /route/optimized
        $data['api_key'] = $this->apiKey;
        $response = $this->client()->post($endpoint, $data);

        return $this->handleResponse($response);
    }

    // DELETE request - for removing data
    public function delete(string $endpoint, array $params = []): array|object
    {
        $params['api_key'] = $this->apiKey;
        $response = $this->client()->delete($endpoint, $params);

        return $this->handleResponse($response);
    }
}

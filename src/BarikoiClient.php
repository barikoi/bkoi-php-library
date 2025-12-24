<?php

namespace Vendor\BarikoiApi;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Vendor\BarikoiApi\Exceptions\BarikoiApiException;
use Vendor\BarikoiApi\Exceptions\BarikoiValidationException;

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
            ->timeout(30)
            ->withHeaders(['Accept' => 'application/json']);
    }

    /**
     * Handle API response and throw appropriate exceptions for errors
     * Returns object (stdClass) if Barikoi API returns object, array if Barikoi returns array
     */
    protected function handleResponse(Response $response): array|object
    {
        if ($response->successful()) {
            $json = $response->json();
            if ($json === null) {
                return [];
            }
            
            // Check if the JSON response is an object structure (associative array with non-numeric keys)
            // If it's an object structure, return as object (stdClass) to match Barikoi API format
            if (is_array($json) && !empty($json)) {
                // Check if it's an associative array (object-like structure)
                $keys = array_keys($json);
                $isAssociative = array_keys($keys) !== $keys; // Keys are not 0,1,2... means it's associative
                
                if ($isAssociative) {
                    // Convert to object (stdClass) to match Barikoi API response format
                    return json_decode(json_encode($json), false);
                }
            }
            
            // Return as array if it's a numeric array (list)
            return $json;
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

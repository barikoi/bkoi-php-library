<?php

namespace Vendor\BarikoiApi\Traits;

use Illuminate\Support\Facades\Http;

trait MakesHttpRequests
{
    /**
     * The Barikoi API key.
     */
    protected string $apiKey;

    /**
     * The Barikoi API base URL.
     */
    protected string $baseUrl;

    /**
     * Make a GET request to the Barikoi API.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     */
    
    protected function get(string $endpoint, array $params = []): array
    {
        $params['api_key'] = $this->apiKey;

        $response = Http::get($this->baseUrl . '/' . ltrim($endpoint, '/'), $params);

        return $response->json() ?? [];
    }

    /**
     * Make a POST request to the Barikoi API.
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    protected function post(string $endpoint, array $data = []): array
    {
        $data['api_key'] = $this->apiKey;

        $response = Http::post($this->baseUrl . '/' . ltrim($endpoint, '/'), $data);

        return $response->json() ?? [];
    }

    /**
     * Make a PUT request to the Barikoi API.
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    protected function put(string $endpoint, array $data = []): array
    {
        $data['api_key'] = $this->apiKey;

        $response = Http::put($this->baseUrl . '/' . ltrim($endpoint, '/'), $data);

        return $response->json() ?? [];
    }

    /**
     * Make a DELETE request to the Barikoi API.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     */
    protected function delete(string $endpoint, array $params = []): array
    {
        $params['api_key'] = $this->apiKey;

        $response = Http::delete($this->baseUrl . '/' . ltrim($endpoint, '/'), $params);

        return $response->json() ?? [];
    }
}

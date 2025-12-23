<?php

namespace Vendor\BarikoiApi\Services;

use Vendor\BarikoiApi\BarikoiClient;
use Vendor\BarikoiApi\Exceptions\BarikoiValidationException;

/**
 * Location Service - Handles geocoding, search, and place APIs
 */
class LocationService
{
    protected BarikoiClient $client;

    public function __construct(BarikoiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Convert coordinates to address (Barikoi V2)
     * Endpoint: GET /v2/api/search/reverse/geocode
     * Docs: Reverse Geocoding (Version 2) [[https://docs.barikoi.com/api#tag/v2.0]]
     *
     * Options: district, post_code, country, sub_district, union, bangla, thana,
     *          country_code, pauroshova, location_type, division, address, area, etc.
     */
    public function reverseGeocode(float $longitude, float $latitude, array $options = []): array
    {
        // add a simple validation or latitude and longitude
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            throw new BarikoiValidationException('Invalid latitude or longitude');
        }
        $params = array_merge([
            'longitude' => $longitude,
            'latitude' => $latitude,
        ], $options);

        // Convert boolean to string for API (true -> 'true')
        $params = array_map(function ($value) {
            return is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }, $params);

        // V2 path: /v2/api/search/reverse/geocode
        return $this->client->get('/v2/api/search/reverse/geocode', $params);
    }

    // Get place suggestions as user types (Barikoi V2 Autocomplete)
    public function autocomplete(string $query, array $options = []): array
    {
        // Only `bangla` option is supported for autocomplete
        $allowedKeys = ['bangla'];
        $invalidKeys = array_diff(array_keys($options), $allowedKeys);

        if (!empty($invalidKeys)) {
            throw new BarikoiValidationException(
                'Invalid autocomplete options: only "bangla" is supported.'
            );
        }

        // V2 path: /v2/api/search/autocomplete [[https://docs.barikoi.com/api#tag/v2.0]]
        return $this->client->get('/v2/api/search/autocomplete', array_merge(['q' => $query], $options));
    }

    // Search for places by query
    public function searchPlace(string $query, array $options = []): array
    {
        // V2 Search Place: GET /v2/api/search-place
        $endpoint = '/api/v2/search-place';
        $params = array_merge([
            'q' => $query,
        ], $options);

        // search-place endpoint uses different base URL structure
        $baseUrl = config('barikoi.base_url');
        $client = new BarikoiClient(config('barikoi.api_key'), $baseUrl);

        return $client->get($endpoint, $params);
    }

    /**
     * Convert address to coordinates (Rupantor engine, Barikoi V2)
     * Endpoint: POST /v2/api/search/rupantor/geocode
     * Only the query (`q`) parameter is supported. No additional options are allowed.
     */
    public function geocode(string $address, array $options = []): array
    {
        // For security and consistency, reject any extra options.
        if (!empty($options)) {
            throw new BarikoiValidationException(
                'Invalid geocode options: only the address (q) parameter is supported.'
            );
        }

        $params = ['q' => $address];

        // V2 path: /v2/api/search/rupantor/geocode [[https://docs.barikoi.com/api#tag/v2.0]]
        return $this->client->post('/v2/api/search/rupantor/geocode', $params);
    }

    // Snap GPS coordinates to nearest road
    // Accepts a single point with latitude and longitude
    public function snapToRoad(float $latitude, float $longitude): array
    {
        // V2 Snap to Road: GET /v2/api/routing/nearest [[https://docs.barikoi.com/api#tag/v2.0]]
        $endpoint = "/v2/api/routing/nearest";
        $params = [
            'point' => $latitude . ',' . $longitude,
            'api_key' => config('barikoi.api_key'),
        ];
        
        // Uses base_url from config (https://barikoi.xyz)
        $baseUrl = config('barikoi.base_url');
        $client = new BarikoiClient(config('barikoi.api_key'), $baseUrl);
        return $client->get($endpoint, $params);
    }

    // Find places within radius
    // Distance is in kilometers (e.g., 0.5 = 500 meters)
    // Limit is the maximum number of results
    // Note: This endpoint uses /v2/api path, so we use a different base URL
    public function nearby(float $longitude, float $latitude, float $distance = 0.5, int $limit = 10, array $options = []): array
    {
        // V2 Nearby: GET /v2/api/search/nearby/{distance}/{limit} [[https://docs.barikoi.com/api#tag/v2.0]]
        $endpoint = "/v2/api/search/nearby/{$distance}/{$limit}";
        $params = array_merge([
            'longitude' => $longitude,
            'latitude' => $latitude,
            'api_key' => config('barikoi.api_key'),
        ], $options);
        
        $baseUrl = config('barikoi.base_url');
        $client = new BarikoiClient(config('barikoi.api_key'), $baseUrl);
        return $client->get($endpoint, $params);
    }
}

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
     * 
     * @return object Returns object (stdClass) matching Barikoi API response format with 'place' and 'status' properties
     */
    public function reverseGeocode(float $longitude, float $latitude, array $options = []): object
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

    /**
     * Get place suggestions as user types (Barikoi V2 Autocomplete)
     * Endpoint: GET /v2/api/search/autocomplete/place
     *
     * Supported options:
     * - bangla        (bool)   Return Bangla fields where available
     * - city          (string) Filter by city name (e.g. "dhaka")
     * - sub_area      (bool)   Include sub-area information
     * - sub_district  (bool)   Include sub-district information
     *
     * @return object Returns stdClass with "places" array (and optional "status")
     */
    public function autocomplete(string $query, array $options = []): object
    {
        $allowedKeys = ['bangla', 'city', 'sub_area', 'sub_district'];
        $invalidKeys = array_diff(array_keys($options), $allowedKeys);

        if (!empty($invalidKeys)) {
            throw new BarikoiValidationException(
                'Invalid autocomplete options: only "bangla", "city", "sub_area", and "sub_district" are supported.'
            );
        }

        $params = array_merge(['q' => $query], $options);

        // Convert boolean options to 'true'/'false' strings for the API
        $params = array_map(function ($value) {
            return is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }, $params);

        // V2 path: /v2/api/search/autocomplete/place [[https://docs.barikoi.com/api#tag/v2.0]]
        return $this->client->get('/v2/api/search/autocomplete/place', $params);
    }

    /**
     * Search for places by query
     * Endpoint: GET /api/v2/search-place
     *
     * Returns object (stdClass) structure from Barikoi:
     * {
     *   "places": [{ "address": "...", "place_code": "..." }, ...],
     *   "session_id": "...",
     *   "status": 200
     * }
     *
     * @return object Returns object (stdClass) matching Barikoi API response format
     */
    public function searchPlace(string $query, array $options = []): object
    {
        // V2 Search Place: GET /api/v2/search-place
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
     * Docs: Rupantor Geocoding [[https://docs.barikoi.com/api#tag/v2.0]]
     *
     * Supported options:
     * - thana     (bool)   Include thana information
     * - district  (bool)   Include district information
     * - bangla    (bool)   Include Bangla address
     *
     * @return object Returns object (stdClass) matching Barikoi API response format
     */
    public function geocode(string $address, array $options = []): object
    {
        $params = array_merge(['q' => $address], $options);

        // Convert boolean to string for API (true -> 'true')
        $params = array_map(function ($value) {
            return is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }, $params);

        // V2 path: /v2/api/search/rupantor/geocode [[https://docs.barikoi.com/api#tag/v2.0]]
        return $this->client->post('/v2/api/search/rupantor/geocode', $params);
    }

    /**
     * Snap GPS coordinates to nearest road
     * Accepts a single point with latitude and longitude
     *
     * Live API returns JSON object:
     * {
     *   "coordinates": [lon, lat],
     *   "distance": 9.17,
     *   "type": "Point"
     * }
     *
     * @return object Returns stdClass matching Barikoi snap-to-road response format
     */
    public function snapToRoad(float $latitude, float $longitude): object
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

    /**
     * Find places within radius
     * Distance is in kilometers (e.g., 0.5 = 500 meters)
     * Limit is the maximum number of results
     *
     * Live API returns JSON object:
     * {
     *   "places": [ { ... }, ... ]
     * }
     *
     * @return object Returns stdClass with "places" array
     */
    public function nearby(float $longitude, float $latitude, float $distance = 0.5, int $limit = 10, array $options = []): object
    {
        // V2 Nearby: GET /v2/api/search/nearby/{distance}/{limit} [[https://docs.barikoi.com/api#tag=v2.0]]
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

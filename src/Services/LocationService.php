<?php

namespace Vendor\BarikoiApi\Services;

use Vendor\BarikoiApi\BarikoiClient;

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
     * Convert coordinates to address
     * Options: district, post_code, country, sub_district, union, bangla, thana,
     *          country_code, pauroshova, location_type, division, address, area, etc.
     */
    public function reverseGeocode(float $longitude, float $latitude, array $options = []): array
    {
        $params = array_merge([
            'longitude' => $longitude,
            'latitude' => $latitude,
        ], $options);

        // Convert boolean to string for API (true -> 'true')
        $params = array_map(function ($value) {
            return is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }, $params);

        return $this->client->get('/search/reverse/geocode', $params);
    }

    // Get place suggestions as user types
    public function autocomplete(string $query, array $options = []): array
    {
        return $this->client->get('/search/autocomplete', array_merge(['q' => $query], $options));
    }

    // Search for places by query
    public function searchPlace(string $query, array $options = []): array
    {
        $params = array_merge([
            'q' => $query,
            'api_key' => config('barikoi.api_key'),
        ], $options);
        
        return $this->client->get('/search-place', $params);
    }

    /**
     * Convert address to coordinates (Rupantor engine)
     * Options: thana, district, bangla
     */
    public function geocode(string $address, array $options = []): array
    {
        $params = array_merge(['q' => $address], $options);
        // Convert boolean to string "yes"/"no" for API (not "true"/"false")
        $params = array_map(function ($value) {
            return is_bool($value) ? ($value ? 'yes' : 'no') : $value;
        }, $params);
        return $this->client->post('/search/rupantor/geocode', $params);
    }

    // Get detailed info about a place by place_code
    // Optional session_id parameter can be provided in options
    public function getPlaceDetails(string $placeCode, array $options = []): array
    {
        $params = array_merge([
            'place_code' => $placeCode,
            'api_key' => config('barikoi.api_key'),
        ], $options);
        return $this->client->get('/places', $params);
    }

    // Snap GPS coordinates to nearest road
    // Accepts a single point with latitude and longitude
    public function snapToRoad(float $latitude, float $longitude): array
    {
        $endpoint = "/v2/api/routing/nearest";
        $params = [
            'point' => $latitude . ',' . $longitude,
            'api_key' => config('barikoi.api_key'),
        ];
        
        // Snap to road endpoint uses different base URL structure
        $baseUrl = 'https://barikoi.xyz';
        $client = new BarikoiClient(config('barikoi.api_key'), $baseUrl);
        return $client->get($endpoint, $params);
    }

    // Find places within radius
    // Distance is in kilometers (e.g., 0.5 = 500 meters)
    // Limit is the maximum number of results
    // Note: This endpoint uses /v2/api path, so we use a different base URL
    public function nearby(float $longitude, float $latitude, float $distance = 0.5, int $limit = 10, array $options = []): array
    {
        $endpoint = "/v2/api/search/nearby/{$distance}/{$limit}";
        $params = array_merge([
            'longitude' => $longitude,
            'latitude' => $latitude,
            'api_key' => config('barikoi.api_key'),
        ], $options);
        
        // Nearby endpoint uses different base URL structure
        $baseUrl = 'https://barikoi.xyz';
        $client = new BarikoiClient(config('barikoi.api_key'), $baseUrl);
        return $client->get($endpoint, $params);
    }

    // Find places of specific category nearby
    // Distance is in kilometers (e.g., 1 = 1000 meters)
    // Note: This endpoint uses /v2/api path, so we use a different base URL
    // Parameter name is 'ptype' not 'category'
    public function nearbyWithCategory(float $longitude, float $latitude, string $category, float $distance = 1.0, int $limit = 10): array
    {
        $endpoint = "/v2/api/search/nearby/category/{$distance}/{$limit}";
        $params = [
            'longitude' => $longitude,
            'latitude' => $latitude,
            'ptype' => $category,  // API uses 'ptype' parameter
            'api_key' => config('barikoi.api_key'),
        ];
        
        // Nearby with category endpoint uses different base URL structure
        $baseUrl = 'https://barikoi.xyz';
        $client = new BarikoiClient(config('barikoi.api_key'), $baseUrl);
        return $client->get($endpoint, $params);
    }

    // Find multiple types of places nearby
    // Distance is in kilometers (e.g., 5 = 5000 meters)
    // Types should be comma-separated in 'q' parameter (e.g., "School,ATM")
    // Note: This endpoint uses /v2/api path, so we use a different base URL
    public function nearbyWithTypes(float $longitude, float $latitude, array $types, float $distance = 5.0, int $limit = 5): array
    {
        if (empty($types)) {
            throw new \InvalidArgumentException('At least one type is required');
        }

        $endpoint = "/v2/api/search/nearby/multi/type/{$distance}/{$limit}";
        $params = [
            'q' => implode(',', $types),  // API uses 'q' parameter for types
            'longitude' => $longitude,
            'latitude' => $latitude,
            'api_key' => config('barikoi.api_key'),
        ];
        
        // Nearby with types endpoint uses different base URL structure
        $baseUrl = 'https://barikoi.xyz';
        $client = new BarikoiClient(config('barikoi.api_key'), $baseUrl);
        return $client->get($endpoint, $params);
    }

    // Check if point is inside polygon boundary
    public function pointInPolygon(float $longitude, float $latitude, array $polygon): array
    {
        return $this->client->post('/point/polygon', [
            'longitude' => $longitude,
            'latitude' => $latitude,
            'polygon' => json_encode($polygon),
        ]);
    }
}

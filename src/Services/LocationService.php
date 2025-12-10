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

    // Convert address to coordinates (Rupantor engine)
    public function geocode(string $address, array $options = []): array
    {
        $params = array_merge(['q' => $address], $options);
        
        // Convert boolean to string "yes"/"no" for API (not "true"/"false")
        $params = array_map(function ($value) {
            return is_bool($value) ? ($value ? 'yes' : 'no') : $value;
        }, $params);
        
        return $this->client->post('/search/rupantor/geocode', $params);
    }

    // Search for places by name or category
    public function searchPlace(string $query, array $options = []): array
    {
        return $this->client->get('/search', array_merge(['q' => $query], $options));
    }

    // Get detailed info about a place by ID
    public function getPlaceDetails(string $placeId): array
    {
        return $this->client->get("/place/{$placeId}");
    }

    // Snap GPS coordinates to nearest road
    public function snapToRoad(array $points): array
    {
        return $this->client->get('/snap/road', ['points' => json_encode($points)]);
    }

    // Find places within radius (meters)
    public function nearby(float $longitude, float $latitude, int $distance = 1000, array $options = []): array
    {
        return $this->client->get('/nearby', array_merge([
            'longitude' => $longitude,
            'latitude' => $latitude,
            'distance' => $distance,
        ], $options));
    }

    // Find places of specific category nearby
    public function nearbyWithCategory(float $longitude, float $latitude, string $category, int $distance = 1000): array
    {
        return $this->client->get('/nearby/category', [
            'longitude' => $longitude,
            'latitude' => $latitude,
            'distance' => $distance,
            'category' => $category,
        ]);
    }

    // Find multiple types of places nearby
    public function nearbyWithTypes(float $longitude, float $latitude, array $types, int $distance = 1000): array
    {
        return $this->client->get('/nearby/types', [
            'longitude' => $longitude,
            'latitude' => $latitude,
            'distance' => $distance,
            'types' => implode(',', $types),
        ]);
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

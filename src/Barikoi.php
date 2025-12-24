<?php

namespace Vendor\BarikoiApi;

use Vendor\BarikoiApi\Services\GeofenceService;
use Vendor\BarikoiApi\Services\LocationService;
use Vendor\BarikoiApi\Services\RouteService;

/**
 * Main Barikoi SDK - Your gateway to all Barikoi services
 */
class Barikoi
{
    protected BarikoiClient $client;
    protected ?LocationService $locationService = null;
    protected ?RouteService $routeService = null;
    protected ?GeofenceService $geofenceService = null;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null)
    {
        $this->client = new BarikoiClient($apiKey, $baseUrl);
    }

    // Get location service (geocoding, search, places)
    public function location(): LocationService
    {
        if (!$this->locationService) {
            $this->locationService = new LocationService($this->client);
        }
        return $this->locationService;
    }

    // Get routing service (directions, optimization)
    public function route(): RouteService
    {
        if (!$this->routeService) {
            $this->routeService = new RouteService($this->client);
        }
        return $this->routeService;
    }

    // Get geofence service (boundaries, zones)
    public function geofence(): GeofenceService
    {
        if (!$this->geofenceService) {
            $this->geofenceService = new GeofenceService($this->client);
        }
        return $this->geofenceService;
    }

    // ============================================================================
    // Shortcut methods - Call location methods directly without location()
    // ============================================================================

    // Convert coordinates to address
    // Returns object (stdClass) matching Barikoi API response format
    public function reverseGeocode(float $longitude, float $latitude, array $options = []): object
    {
        return $this->location()->reverseGeocode($longitude, $latitude, $options);
    }

    // Get place suggestions as user types
    // Returns object (stdClass) matching Barikoi autocomplete response format
    public function autocomplete(string $query, array $options = []): object
    {
        return $this->location()->autocomplete($query, $options);
    }

    // Search for places by query
    // Returns object (stdClass) matching Barikoi search-place API response format
    public function searchPlace(string $query, array $options = []): object
    {
        return $this->location()->searchPlace($query, $options);
    }

    // Convert address to coordinates (Rupantor)
    // Returns object (stdClass) matching Barikoi API response format
    public function geocode(string $address, array $options = []): object
    {
        return $this->location()->geocode($address, $options);
    }

    // Get place details by place_code
    // Optional session_id can be provided in options
    public function getPlaceDetails(string $placeCode, array $options = []): array
    {
        return $this->location()->getPlaceDetails($placeCode, $options);
    }

    // Alias for getPlaceDetails to support Barikoi::placeDetails(...)
    public function placeDetails(string $placeCode, array $options = []): array
    {
        return $this->getPlaceDetails($placeCode, $options);
    }

    // Snap GPS coordinates to nearest road
    public function snapToRoad(float $latitude, float $longitude): object
    {
        return $this->location()->snapToRoad($latitude, $longitude);
    }

    // Find places within radius
    // Distance in kilometers (e.g., 0.5 = 500 meters), limit is max results
    public function nearby(float $longitude, float $latitude, float $distance = 0.5, int $limit = 10, array $options = []): object
    {
        return $this->location()->nearby($longitude, $latitude, $distance, $limit, $options);
    }

    // Check nearby location within a specified radius
    // Determine if a current location is within a specified radius of a destination point
    // Radius in meters (default 50m)
    public function checkNearby(float $destinationLatitude,float $destinationLongitude,float $currentLatitude,float $currentLongitude,float $radius = 50): array
    {
        return $this->geofence()->checkNearby($destinationLatitude, $destinationLongitude, $currentLatitude, $currentLongitude, $radius);
    }

    // Find places of specific category nearby
    // Distance in kilometers (e.g., 1.0 = 1000 meters), limit is max results
    public function nearbyWithCategory(float $longitude, float $latitude, string $category, float $distance = 1.0, int $limit = 10): array
    {
        return $this->location()->nearbyWithCategory($longitude, $latitude, $category, $distance, $limit);
    }

    // Find multiple types of places nearby
    // Distance in kilometers (e.g., 5.0 = 5000 meters), limit is max results
    public function nearbyWithTypes(float $longitude, float $latitude, array $types, float $distance = 5.0, int $limit = 5): array
    {
        return $this->location()->nearbyWithTypes($longitude, $latitude, $types, $distance, $limit);
    }

    // Check if point is inside polygon
    public function pointInPolygon(float $longitude, float $latitude, array $polygon): array
    {
        return $this->location()->pointInPolygon($longitude, $latitude, $polygon);
    }

    // ============================================================================
    // Shortcut methods - Call route methods directly
    // ============================================================================

    // Simple route overview between multiple points
    // Returns object (stdClass) matching Barikoi route API response format
    public function routeOverview(array $points, array $options = []): object
    {
        return $this->route()->routeOverview($points, $options);
    }

    // Calculate detailed route between points (turn-by-turn style response)
    // Returns object (stdClass) matching Barikoi route API response format
    public function calculateRoute(array $points, array $options = []): object
    {
        return $this->route()->detailed($points, $options);
    }

    // Calculate detailed navigation route (separate routing API)
    // Calculate detailed navigation route (separate routing API, returns stdClass with \"trip\")
    public function detailedNavigation(
        float $startLatitude,
        float $startLongitude,
        float $destinationLatitude,
        float $destinationLongitude,
        array $options = []
    ): object {
        return $this->route()->calculateRoute(
            $startLatitude,
            $startLongitude,
            $destinationLatitude,
            $destinationLongitude,
            $options
        );
    }

    // Calculate optimized route with waypoints (up to 50)
    public function optimizedRoute(string $source, string $destination, array $waypoints = [], array $options = []): array
    {
        return $this->route()->optimizedRoute($source, $destination, $waypoints, $options);
    }

    // Optimize route for multiple waypoints (TSP solution)
    public function routeOptimize(array $points, array $options = []): array
    {
        return $this->route()->routeOptimize($points, $options);
    }
}

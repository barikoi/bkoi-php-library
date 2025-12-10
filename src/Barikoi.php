<?php

namespace Vendor\BarikoiApi;

use Vendor\BarikoiApi\Services\AdministrativeService;
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
    protected ?AdministrativeService $administrativeService = null;
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

    // Get administrative service (divisions, districts, thanas)
    public function administrative(): AdministrativeService
    {
        if (!$this->administrativeService) {
            $this->administrativeService = new AdministrativeService($this->client);
        }
        return $this->administrativeService;
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
    public function reverseGeocode(float $longitude, float $latitude, array $options = []): array
    {
        return $this->location()->reverseGeocode($longitude, $latitude, $options);
    }

    // Get place suggestions as user types
    public function autocomplete(string $query, array $options = []): array
    {
        return $this->location()->autocomplete($query, $options);
    }

    // Convert address to coordinates
    public function geocode(string $address, array $options = []): array
    {
        return $this->location()->geocode($address, $options);
    }

    // Search for places
    public function searchPlace(string $query, array $options = []): array
    {
        return $this->location()->searchPlace($query, $options);
    }

    // Get place details by ID
    public function getPlaceDetails(string $placeId): array
    {
        return $this->location()->getPlaceDetails($placeId);
    }

    // Snap GPS coordinates to nearest road
    public function snapToRoad(array $points): array
    {
        return $this->location()->snapToRoad($points);
    }

    // Find places within radius
    public function nearby(float $longitude, float $latitude, int $distance = 1000, array $options = []): array
    {
        return $this->location()->nearby($longitude, $latitude, $distance, $options);
    }

    // Find places of specific category nearby
    public function nearbyWithCategory(float $longitude, float $latitude, string $category, int $distance = 1000): array
    {
        return $this->location()->nearbyWithCategory($longitude, $latitude, $category, $distance);
    }

    // Find multiple types of places nearby
    public function nearbyWithTypes(float $longitude, float $latitude, array $types, int $distance = 1000): array
    {
        return $this->location()->nearbyWithTypes($longitude, $latitude, $types, $distance);
    }

    // Check if point is inside polygon
    public function pointInPolygon(float $longitude, float $latitude, array $polygon): array
    {
        return $this->location()->pointInPolygon($longitude, $latitude, $polygon);
    }

    // ============================================================================
    // Shortcut methods - Call route methods directly
    // ============================================================================

    // Calculate detailed route with navigation instructions (bike, motorcycle, car)
    public function detailedNavigation(
        float $startLatitude,
        float $startLongitude,
        float $destinationLatitude,
        float $destinationLongitude,
        array $options = []
    ): array {
        return $this->route()->detailedNavigation(
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
}

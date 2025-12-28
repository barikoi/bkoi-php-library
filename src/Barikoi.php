<?php

namespace Vendor\BarikoiApi;

use Vendor\BarikoiApi\Exceptions\BarikoiValidationException;
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
    public function checkNearby(float $destinationLatitude,float $destinationLongitude,float $currentLatitude,float $currentLongitude,float $radius = 50): array|object
    {
        return $this->geofence()->checkNearby($destinationLatitude, $destinationLongitude, $currentLatitude, $currentLongitude, $radius);
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

    // Calculate detailed route with navigation instructions
    // Returns object (stdClass) matching Barikoi routing API response format (with "trip" object)
    // Accepts start/destination object format
    public function calculateRoute(array $startDestination, array $options = []): object
    {
        // Validate and extract coordinates from start/destination pattern
        $this->validateStartDestinationFormat($startDestination);
        
        $start = $startDestination['start'];
        $destination = $startDestination['destination'];
        
        // Call RouteService::calculateRoute with individual coordinates
        return $this->route()->calculateRoute(
            $start['latitude'],
            $start['longitude'],
            $destination['latitude'],
            $destination['longitude'],
            $options
        );
    }
    
    /**
     * Validate start/destination format for calculateRoute
     *
     * @param array $data Array with 'start' and 'destination' keys
     * @throws BarikoiValidationException
     */
    protected function validateStartDestinationFormat(array $data): void
    {
        if (!isset($data['start']) || !is_array($data['start'])) {
            throw new BarikoiValidationException(
                'Invalid format: "start" key is required and must be an array with "longitude" and "latitude" keys.'
            );
        }
        
        if (!isset($data['destination']) || !is_array($data['destination'])) {
            throw new BarikoiValidationException(
                'Invalid format: "destination" key is required and must be an array with "longitude" and "latitude" keys.'
            );
        }
        
        $start = $data['start'];
        $destination = $data['destination'];
        
        // Validate start coordinates
        if (!isset($start['longitude']) || !isset($start['latitude'])) {
            throw new BarikoiValidationException(
                'Invalid format: "start" must contain "longitude" and "latitude" keys.'
            );
        }
        
        if (!is_numeric($start['longitude']) || !is_numeric($start['latitude'])) {
            throw new BarikoiValidationException(
                'Invalid coordinates: "start" longitude and latitude must be numeric.'
            );
        }
        
        // Validate destination coordinates
        if (!isset($destination['longitude']) || !isset($destination['latitude'])) {
            throw new BarikoiValidationException(
                'Invalid format: "destination" must contain "longitude" and "latitude" keys.'
            );
        }
        
        if (!is_numeric($destination['longitude']) || !is_numeric($destination['latitude'])) {
            throw new BarikoiValidationException(
                'Invalid coordinates: "destination" longitude and latitude must be numeric.'
            );
        }
        
        // Validate coordinate ranges
        if ($start['latitude'] < -90 || $start['latitude'] > 90 || 
            $start['longitude'] < -180 || $start['longitude'] > 180) {
            throw new BarikoiValidationException(
                'Invalid coordinates: "start" latitude must be between -90 and 90, longitude between -180 and 180.'
            );
        }
        
        if ($destination['latitude'] < -90 || $destination['latitude'] > 90 || 
            $destination['longitude'] < -180 || $destination['longitude'] > 180) {
            throw new BarikoiValidationException(
                'Invalid coordinates: "destination" latitude must be between -90 and 90, longitude between -180 and 180.'
            );
        }
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
}
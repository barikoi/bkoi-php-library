<?php

namespace Barikoi\BarikoiApis\Services;

use Barikoi\BarikoiApis\BarikoiClient;
use Barikoi\BarikoiApis\Exceptions\BarikoiValidationException;

class RouteService
{
    protected BarikoiClient $client;

    /**
     * Valid transportation profiles for /route endpoint
     */
    const PROFILE_CAR = 'car';
    const PROFILE_FOOT = 'foot';

    /**
     * Valid profiles for /route endpoint
     */
    protected array $validProfiles = [self::PROFILE_CAR, self::PROFILE_FOOT];

    public function __construct(BarikoiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Validate and set default profile for /route endpoint
     *
     * @param array $options
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function validateProfile(array $options): array
    {
        // Set default profile if not specified
        if (!isset($options['profile'])) {
            $options['profile'] = self::PROFILE_CAR;
        }

        // Validate profile
        if (!in_array($options['profile'], $this->validProfiles)) {
            throw new \InvalidArgumentException(
                "Invalid profile '{$options['profile']}'. Accepted values are: " . implode(', ', $this->validProfiles)
            );
        }

        return $options;
    }

    /**
     * Validate that all points have valid latitude/longitude
     *
     * @param array $points
     * @return void
     * @throws BarikoiValidationException
     */
    protected function validatePoints(array $points): void
    {
        foreach ($points as $point) {
            $lon = $point['longitude'] ?? null;
            $lat = $point['latitude'] ?? null;

            if (!is_numeric($lon) || !is_numeric($lat)) {
                throw new BarikoiValidationException('Invalid coordinates: longitude and latitude must be numeric.');
            }

            if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
                throw new BarikoiValidationException('Invalid coordinates: latitude must be between -90 and 90, longitude between -180 and 180.');
            }
        }
    }

    /**
     * Route Overview - Get simple route between points
     *
     * Calculate a basic route between two or more points. Returns route geometry
     * and basic information like distance and duration.
     *
     * @param array $points Array of coordinate points for the route
     *                      Example: [['longitude' => 90.3572, 'latitude' => 23.8067], ...]
     * @param array $options Optional parameters:
     *                       - profile (string): 'car' (default) or 'foot'
     *                       - geometries (string): 'polyline' or 'geojson'
     * @return object Returns stdClass matching Barikoi route API response format
     * @throws \InvalidArgumentException If invalid profile is provided
     *
     * @example
     * ```php
     * // Car route (default)
     * $route = Barikoi::routeOverview([
     *     ['longitude' => 90.3572, 'latitude' => 23.8067],
     *     ['longitude' => 90.3680, 'latitude' => 23.8100]
     * ]);
     *
     * // Walking route
     * $route = Barikoi::routeOverview([
     *     ['longitude' => 90.3572, 'latitude' => 23.8067],
     *     ['longitude' => 90.3680, 'latitude' => 23.8100]
     * ], ['profile' => 'foot']);
     * ```
     */
    public function routeOverview(array $points, array $options = []): object
    {
        // Validate coordinates and profile
        $this->validatePoints($points);
        $options = $this->validateProfile($options);

        // Convert points to URL path format: lon,lat;lon,lat
        $coordinates = implode(';', array_map(function ($point) {
            return "{$point['longitude']},{$point['latitude']}";
        }, $points));

        // Add default geometries parameter if not specified
        if (!isset($options['geometries'])) {
            $options['geometries'] = 'polyline';
        }

        return $this->client->get("/v2/api/route/{$coordinates}", $options);
    }

    /**
     * Calculate Detailed Route
     *
     * Calculate a detailed route with turn-by-turn directions and comprehensive
     * information including maneuvers, street names, and step-by-step instructions.
     *
     * @param array $points Array of coordinate points for the route
     *                      Example: [['longitude' => 90.3572, 'latitude' => 23.8067], ...]
     * @param array $options Optional parameters:
     *                       - profile (string): 'car' (default) or 'foot'
     *                       - alternatives (bool): Return alternative routes
     *                       - steps (bool): Include step-by-step instructions
     *                       - overview (string): Geometry overview detail level
     *                       - geometries (string): 'polyline' or 'geojson'
     * @return object Returns object (stdClass) matching Barikoi route API response format
     * @throws \InvalidArgumentException If invalid profile is provided
     *
     * @example
     * ```php
     * // Car route with turn-by-turn
     * $detailedRoute = Barikoi::calculateRoute([
     *     ['longitude' => 90.3572, 'latitude' => 23.8067],
     *     ['longitude' => 90.3680, 'latitude' => 23.8100]
     * ], ['alternatives' => true, 'steps' => true]);
     *
     * // Walking route
     * $walkingRoute = Barikoi::calculateRoute([
     *     ['longitude' => 90.3572, 'latitude' => 23.8067],
     *     ['longitude' => 90.3680, 'latitude' => 23.8100]
     * ], ['profile' => 'foot', 'steps' => true]);
     * ```
     */
    public function detailed(array $points, array $options = []): object
    {
        // Validate coordinates and profile
        $this->validatePoints($points);
        $options = $this->validateProfile($options);

        // Convert points to URL path format: lon,lat;lon,lat
        $coordinates = implode(';', array_map(function ($point) {
            return "{$point['longitude']},{$point['latitude']}";
        }, $points));

        // Add default parameters for detailed route
        $params = array_merge([
            'geometries' => 'polyline',
        ], $options);

        // Convert boolean flags to 'true'/'false' strings if present
        foreach (['steps', 'alternatives'] as $flag) {
            if (array_key_exists($flag, $params) && is_bool($params[$flag])) {
                $params[$flag] = $params[$flag] ? 'true' : 'false';
            }
        }

        // Use the v2 routing endpoint (same base as routeOverview)
        return $this->client->get("/v2/api/route/{$coordinates}", $params);
    }

    /**
     * Route Optimization - Optimize route for multiple waypoints
     *
     * Optimize the visiting order of multiple waypoints to minimize travel time
     * or distance. This is the Traveling Salesman Problem (TSP) solution,
     * useful for delivery route planning.
     *
     * @param array $points Array of coordinate points to visit
     *                      Example: [['longitude' => 90.3572, 'latitude' => 23.8067], ...]
     * @param array $options Optional parameters:
     *                       - profile (string): 'car' (default), 'bike', or 'motorcycle'
     * @return array Response containing optimized route including waypoints
     * @throws \InvalidArgumentException If invalid profile is provided or not enough points
     *
     * @example
     * ```php
     * // Optimize car route
     * $optimized = Barikoi::route()->optimize([
     *     ['longitude' => 90.3572, 'latitude' => 23.8067],
     *     ['longitude' => 90.3680, 'latitude' => 23.8100],
     *     ['longitude' => 90.3750, 'latitude' => 23.8150]
     * ]);
     *
     * // Optimize walking route
     * $optimized = Barikoi::route()->optimize([
     *     ['longitude' => 90.3572, 'latitude' => 23.8067],
     *     ['longitude' => 90.3680, 'latitude' => 23.8100]
     * ], ['profile' => 'foot']);
     * `optimize `
     * Route Location Optimized
     *
     * Optimize routes with location-specific preferences and constraints.
     * Advanced optimization considering location priorities and requirements.
     *
     * @param array $points Array of coordinate points with location details
     * @param array $options Optional parameters:
     *                       - profile (string): 'car' (default) or 'foot'
     * @return array Response containing location-optimized route
     * @throws \InvalidArgumentException If invalid profile is provided
     *
     * @example
     * ```php
     * $optimized = Barikoi::route()->locationOptimized([
     *     ['longitude' => 90.3572, 'latitude' => 23.8067],
     *     ['longitude' => 90.3680, 'latitude' => 23.8100]
     * ], ['profile' => 'car']);
     * ```
     */
    public function locationOptimized(array $points, array $options = []): array
    {
        // Validate profile
        $options = $this->validateProfile($options);

        $data = array_merge([
            'points' => json_encode($points),
        ], $options);

        return $this->client->post('/route/location/optimize', $data);
    }

    /**
     * Route Match - Match GPS trace to road network
     *
     * Match a series of GPS coordinates to the most likely path on the road network.
     * Useful for cleaning up GPS traces and reconstructing traveled routes.
     * Also known as map matching.
     *
     * @param array $points Array of GPS coordinate points to match
     *                      Example: [['longitude' => 90.3572, 'latitude' => 23.8067], ...]
     * @param array $options Optional parameters:
     *                       - radiuses (array): Search radius for each point
     *                       - timestamps (array): Timestamp for each coordinate
     *                       - geometries (string): 'polyline' or 'geojson'
     * @return array Response containing matched route geometry and coordinates
     *
     * @example
     * ```php
     * $matched = Barikoi::route()->match([
     *     ['longitude' => 90.3572, 'latitude' => 23.8067],
     *     ['longitude' => 90.3575, 'latitude' => 23.8068],
     *     ['longitude' => 90.3578, 'latitude' => 23.8069]
     * ]);
     * ```
     */
    public function match(array $points, array $options = []): array
    {
        // Validate coordinates
        $this->validatePoints($points);

        // Convert points to URL path format: lon,lat;lon,lat
        $coordinates = implode(';', array_map(function ($point) {
            return "{$point['longitude']},{$point['latitude']}";
        }, $points));

        // Add default geometries parameter if not specified
        if (!isset($options['geometries'])) {
            $options['geometries'] = 'polyline';
        }

        return $this->client->get("/match/{$coordinates}", $options);
    }

    /**
     * Calculate distance between two points
     *
     * Shortcut method to calculate distance between two coordinates
     *
     * @param float $fromLongitude Starting point longitude
     * @param float $fromLatitude Starting point latitude
     * @param float $toLongitude Ending point longitude
     * @param float $toLatitude Ending point latitude
     * @param array $options Optional parameters (profile: 'car' or 'foot')
     * @return object Returns stdClass matching Barikoi route API response format
     * @throws \InvalidArgumentException If invalid profile is provided
     */
    public function distance(float $fromLongitude, float $fromLatitude, float $toLongitude, float $toLatitude, array $options = []): object
    {
        $points = [
            ['longitude' => $fromLongitude, 'latitude' => $fromLatitude],
            ['longitude' => $toLongitude, 'latitude' => $toLatitude],
        ];

        return $this->routeOverview($points, $options);
    }

    /**
     * Get directions between two points
     *
     * Shortcut method to get detailed directions between coordinates
     *
     * @param float $fromLongitude Starting point longitude
     * @param float $fromLatitude Starting point latitude
     * @param float $toLongitude Ending point longitude
     * @param float $toLatitude Ending point latitude
     * @param array $options Optional parameters (profile: 'car' or 'foot')
     * @return object Returns object (stdClass) matching Barikoi route API response format
     * @throws \InvalidArgumentException If invalid profile is provided
     */
    public function directions(float $fromLongitude, float $fromLatitude, float $toLongitude, float $toLatitude, array $options = []): object
    {
        $points = [
            ['longitude' => $fromLongitude, 'latitude' => $fromLatitude],
            ['longitude' => $toLongitude, 'latitude' => $toLatitude],
        ];

        return $this->detailed($points, $options);
    }

    /**
     * Calculate Detailed Route with Navigation Instructions
     * 
     * This is a separate routing API that provides detailed step-by-step navigation
     * instructions with support for bike, motorcycle, and car profiles.
     *
     * @param float $startLatitude Starting point latitude
     * @param float $startLongitude Starting point longitude
     * @param float $destinationLatitude Destination point latitude
     * @param float $destinationLongitude Destination point longitude
     * @param array $options Optional parameters:
     *                       - profile (string): 'bike', 'motorcycle' (default), or 'car'
     *                       - type (string): Route type - 'vh' (default, motorcycle only) or 'gh' (all profiles)
     *                       - country_code (string): ISO Alpha-3 country code (default: 'bgd')
     * @return object Returns stdClass matching Barikoi routing API response format (with \"trip\" object)
     * @throws \InvalidArgumentException If invalid profile, type, or unsupported profile-type combination
     *
     * @example
     * ```php
     * // Motorcycle route (default, type 'vh')
     * $route = Barikoi::route()->detailedNavigation(
     *     23.791645, 90.365588,  // Start: Lat, Lng
     *     23.784715, 90.367630   // Destination: Lat, Lng
     * );
     *
     * // Car route with 'gh' type
     * $carRoute = Barikoi::route()->detailedNavigation(
     *     23.791645, 90.365588,
     *     23.784715, 90.367630,
     *     ['type' => 'gh', 'profile' => 'car']
     * );
     *
     * // Bike route with 'gh' type
     * $bikeRoute = Barikoi::route()->detailedNavigation(
     *     23.791645, 90.365588,
     *     23.784715, 90.367630,
     *     ['type' => 'gh', 'profile' => 'bike']
     * );
     * ```
     */
    public function calculateRoute(
        float $startLatitude,
        float $startLongitude,
        float $destinationLatitude,
        float $destinationLongitude,
        array $options = []
    ): object {
        // Define supported profiles for each type
        $support = [
            'vh' => ['motorcycle'],
            'gh' => ['motorcycle', 'car', 'bike'],
        ];

        // Valid types and profiles
        $validTypes = array_keys($support);
        $validProfiles = ['bike', 'motorcycle', 'car'];

        // Get type and profile from options
        $type = $options['type'] ?? 'vh';
        $profile = $options['profile'] ?? 'motorcycle';

        // Validate type
        if (!in_array($type, $validTypes)) {
            return (object) [
                'status' => 400,
                'error' => 'invalid_type',
                'message' => "Type '{$type}' is not valid",
                'supported_types' => $validTypes
            ];
        }

        // Validate profile
        if (!in_array($profile, $validProfiles)) {
            return (object) [
                'status' => 400,
                'error' => 'invalid_profile',
                'message' => "Profile '{$profile}' is not valid",
                'supported_profiles' => $validProfiles
            ];
        }

        // Cross-validate: Check if profile is supported by the selected type
        if (!in_array($profile, $support[$type])) {
            return (object) [
                'status' => 400,
                'error' => 'unsupported_combination',
                'message' => "Profile '{$profile}' not supported for type '{$type}'",
                'type' => $type,
                'profile' => $profile,
                'supported_profiles' => $support[$type]
            ];
        }

        // Build query parameters
        $queryParams = [
            'type' => $type,
            'profile' => $profile,
        ];

        if (isset($options['country_code'])) {
            $queryParams['country_code'] = $options['country_code'];
        }

        // Build request data
        $data = [
            'data' => [
                'start' => [
                    'latitude' => $startLatitude,
                    'longitude' => $startLongitude,
                ],
                'destination' => [
                    'latitude' => $destinationLatitude,
                    'longitude' => $destinationLongitude,
                ],
            ],
        ];

        // Make POST request with JSON body to v2 routing endpoint
        $endpoint = '/v2/api/routing?' . http_build_query($queryParams);
        return $this->client->postJson($endpoint, $data);
    }
}


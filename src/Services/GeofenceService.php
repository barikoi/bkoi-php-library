<?php

namespace Vendor\BarikoiApi\Services;

use Vendor\BarikoiApi\BarikoiClient;
use Vendor\BarikoiApi\Exceptions\BarikoiValidationException;

/**
 * Geofence Service
 *
 * Provides geofencing capabilities to create virtual boundaries and check
 * whether coordinates fall within those boundaries. Useful for location-based
 * alerts, delivery zones, and proximity detection.
 */
class GeofenceService
{
    protected BarikoiClient $client;

    public function __construct(BarikoiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Check nearby location within a specified radius
     *
     * Determine if a current location is within a specified radius of a
     * destination point. Throws BarikoiValidationException for invalid coordinates or radius.
     *
     * @param float $destinationLatitude The latitude of the destination/target point
     * @param float $destinationLongitude The longitude of the destination/target point
     * @param int $radius The proximity radius in meters (must be positive)
     * @param float $currentLatitude The latitude of the current/checking point
     * @param float $currentLongitude The longitude of the current/checking point
     * @return array Response indicating whether current location is within radius
     * @throws \Vendor\BarikoiApi\Exceptions\BarikoiValidationException
     *
     * @example
     * ```php
     * $result = Barikoi::geofence()->checkNearby(
     *     23.8067,  // destination latitude
     *     90.3572,  // destination longitude
     *     100,      // radius in meters
     *     23.8070,  // current latitude
     *     90.3575   // current longitude
     * );
     * // Returns: ['is_nearby' => true/false, 'distance' => ...]
     * ```
     */
    public function checkNearby(float $destinationLatitude,float $destinationLongitude,float $currentLatitude,float $currentLongitude,float $radius = 50): array|object
    {
        // Validate destination coordinates
        if ($destinationLatitude < -90 || $destinationLatitude > 90 || $destinationLongitude < -180 || $destinationLongitude > 180) {
            throw new BarikoiValidationException('Invalid destination latitude or longitude');
        }
        // Validate current coordinates
        if ($currentLatitude < -90 || $currentLatitude > 90 || $currentLongitude < -180 || $currentLongitude > 180) {
            throw new BarikoiValidationException('Invalid current latitude or longitude');
        }
        if ($radius <= 0) {
            throw new BarikoiValidationException('Radius must be positive');
        }

        $params = [
            'api_key' => config('barikoi.api_key'),
            'destination_latitude' => $destinationLatitude,
            'destination_longitude' => $destinationLongitude,
            'current_latitude' => $currentLatitude,
            'current_longitude' => $currentLongitude,
            'radius' => $radius,
        ];

        $baseUrl = config('barikoi.base_url');

        $client = new BarikoiClient(config('barikoi.api_key'), $baseUrl);

        return  $client->get('/v2/api/check/nearby', $params);
    }

}

<?php

namespace Vendor\BarikoiApi\Services;

use Vendor\BarikoiApi\BarikoiClient;

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
     * destination point. Useful for proximity detection and arrival notifications.
     *
     * @param float $destinationLatitude The latitude of the destination/target point
     * @param float $destinationLongitude The longitude of the destination/target point
     * @param int $radius The proximity radius in meters
     * @param float $currentLatitude The latitude of the current/checking point
     * @param float $currentLongitude The longitude of the current/checking point
     * @return array Response indicating whether current location is within radius
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
     * // Returns: ['inside' => true/false, 'distance' => ...]
     * ```
     */
    public function checkNearby(
        float $destinationLatitude,
        float $destinationLongitude,
        int $radius,
        float $currentLatitude,
        float $currentLongitude
    ): array {
        // V2: GET /v2/api/check/nearby
        return $this->client->get('/v2/api/check/nearby', [
            'destination_latitude' => $destinationLatitude,
            'destination_longitude' => $destinationLongitude,
            'radius' => $radius,
            'current_latitude' => $currentLatitude,
            'current_longitude' => $currentLongitude,
        ]);
    }
}


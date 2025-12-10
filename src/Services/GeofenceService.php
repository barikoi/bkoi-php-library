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
     * Set geofence point
     *
     * Create a new geofence with a circular boundary defined by a center point
     * and radius. This creates a virtual perimeter around a location.
     *
     * @param string $name The name/label for this geofence
     * @param float $longitude The longitude of the geofence center point
     * @param float $latitude The latitude of the geofence center point
     * @param int $radius The radius of the geofence in meters
     * @return array Response containing the created geofence information with ID
     *
     * @example
     * ```php
     * $geofence = Barikoi::geofence()->setPoint(
     *     'Office Location',
     *     90.3572,
     *     23.8067,
     *     100  // 100 meters radius
     * );
     * ```
     */
    public function setPoint(string $name, float $longitude, float $latitude, int $radius): array
    {
        $data = [
            'name' => $name,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'radius' => $radius,
        ];

        return $this->client->post('/set-geo-fence-point', $data);
    }

    /**
     * Get all geofence points
     *
     * Retrieve a list of all geofences created under your API key.
     *
     * @return array Response containing array of all geofence points
     *
     * @example
     * ```php
     * $geofences = Barikoi::geofence()->getPoints();
     * ```
     */
    public function getPoints(): array
    {
        return $this->client->get('/get-geo-fence-points');
    }

    /**
     * Get geofence point by ID
     *
     * Retrieve detailed information about a specific geofence using its ID.
     *
     * @param string $id The unique identifier of the geofence
     * @return array Response containing geofence details
     *
     * @example
     * ```php
     * $geofence = Barikoi::geofence()->getPoint('geofence_id_here');
     * ```
     */
    public function getPoint(string $id): array
    {
        return $this->client->get("/get-geo-fence-point/{$id}");
    }

    /**
     * Update geofence point by ID
     *
     * Modify an existing geofence's radius and/or name. Useful for adjusting
     * boundaries without recreating the geofence.
     *
     * @param string $id The unique identifier of the geofence to update
     * @param int $radius The new radius in meters
     * @param string|null $name Optional new name for the geofence
     * @return array Response containing updated geofence information
     *
     * @example
     * ```php
     * $updated = Barikoi::geofence()->updatePoint(
     *     'geofence_id',
     *     150,  // New radius: 150 meters
     *     'Updated Office Location'  // Optional new name
     * );
     * ```
     */
    public function updatePoint(string $id, int $radius, ?string $name = null): array
    {
        $data = ['radius' => $radius];

        if ($name) {
            $data['name'] = $name;
        }

        return $this->client->post("/update-geo-fence-point/{$id}", $data);
    }

    /**
     * Delete geofence point by ID
     *
     * Permanently remove a geofence. This action cannot be undone.
     *
     * @param string $id The unique identifier of the geofence to delete
     * @return array Response confirming deletion
     *
     * @example
     * ```php
     * $deleted = Barikoi::geofence()->deletePoint('geofence_id');
     * ```
     */
    public function deletePoint(string $id): array
    {
        return $this->client->delete("/delete-geo-fence-point/{$id}");
    }

    /**
     * Check if coordinates are inside any geofence
     *
     * Determine whether the given coordinates fall within any of your
     * defined geofences. Returns information about matched geofences.
     *
     * @param float $longitude The longitude to check
     * @param float $latitude The latitude to check
     * @return array Response indicating whether the point is inside any geofence
     *
     * @example
     * ```php
     * $result = Barikoi::geofence()->checkGeofence(90.3572, 23.8067);
     * // Returns: ['inside' => true/false, 'geofences' => [...]]
     * ```
     */
    public function checkGeofence(float $longitude, float $latitude): array
    {
        return $this->client->get('/check-geo-fence', [
            'longitude' => $longitude,
            'latitude' => $latitude,
        ]);
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
        return $this->client->get('/check/nearby', [
            'destination_latitude' => $destinationLatitude,
            'destination_longitude' => $destinationLongitude,
            'radius' => $radius,
            'current_latitude' => $currentLatitude,
            'current_longitude' => $currentLongitude,
        ]);
    }
}


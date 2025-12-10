<?php

namespace Vendor\BarikoiApi\Services;

use Vendor\BarikoiApi\BarikoiClient;

/**
 * Administrative Service
 *
 * Provides access to Bangladesh administrative boundary data including
 * divisions, districts, subdistricts, thanas, unions, areas, wards, zones,
 * and city corporation information.
 */
class AdministrativeService
{
    protected BarikoiClient $client;

    public function __construct(BarikoiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get all Divisions
     *
     * Retrieve a list of all divisions in Bangladesh. Divisions are the
     * highest level administrative regions in Bangladesh.
     *
     * @return array Response containing array of all divisions
     *
     * @example
     * ```php
     * $divisions = Barikoi::administrative()->getDivisions();
     * ```
     */
    public function getDivisions(): array
    {
        return $this->client->get('/divisions');
    }

    /**
     * Get all Districts
     *
     * Retrieve a list of all districts in Bangladesh, optionally filtered by division.
     * Districts (Zilla) are the second-level administrative divisions.
     *
     * @param string|null $division Optional division name to filter districts
     * @return array Response containing array of districts
     *
     * @example
     * ```php
     * // Get all districts
     * $allDistricts = Barikoi::administrative()->getDistricts();
     *
     * // Get districts in Dhaka division
     * $dhakaDistricts = Barikoi::administrative()->getDistricts('Dhaka');
     * ```
     */
    public function getDistricts(?string $division = null): array
    {
        $params = $division ? ['division' => $division] : [];
        return $this->client->get('/districts', $params);
    }

    /**
     * Get all Subdistricts (Upazilas)
     *
     * Retrieve a list of all subdistricts/upazilas, optionally filtered by district.
     * Subdistricts (Upazila) are the third-level administrative divisions.
     *
     * @param string|null $district Optional district name to filter subdistricts
     * @return array Response containing array of subdistricts
     *
     * @example
     * ```php
     * // Get all subdistricts
     * $allSubdistricts = Barikoi::administrative()->getSubdistricts();
     *
     * // Get subdistricts in Dhaka district
     * $dhakaSubdistricts = Barikoi::administrative()->getSubdistricts('Dhaka');
     * ```
     */
    public function getSubdistricts(?string $district = null): array
    {
        $params = $district ? ['district' => $district] : [];
        return $this->client->get('/subdistricts', $params);
    }

    /**
     * Get all Thanas
     *
     * Retrieve a list of all thanas (police stations), optionally filtered by district.
     * Thanas are administrative and law enforcement units.
     *
     * @param string|null $district Optional district name to filter thanas
     * @return array Response containing array of thanas
     *
     * @example
     * ```php
     * $thanas = Barikoi::administrative()->getThanas('Dhaka');
     * ```
     */
    public function getThanas(?string $district = null): array
    {
        $params = $district ? ['district' => $district] : [];
        return $this->client->get('/thanas', $params);
    }

    /**
     * Get City with Areas
     *
     * Retrieve cities along with their associated areas/neighborhoods.
     *
     * @param string|null $city Optional city name to get specific city's areas
     * @return array Response containing city and area information
     *
     * @example
     * ```php
     * $cityAreas = Barikoi::administrative()->getCityWithAreas('Dhaka');
     * ```
     */
    public function getCityWithAreas(?string $city = null): array
    {
        $params = $city ? ['city' => $city] : [];
        return $this->client->get('/city/areas', $params);
    }

    /**
     * Get Unions
     *
     * Retrieve a list of unions, optionally filtered by subdistrict.
     * Unions are the smallest rural administrative units in Bangladesh.
     *
     * @param string|null $subdistrict Optional subdistrict name to filter unions
     * @return array Response containing array of unions
     *
     * @example
     * ```php
     * $unions = Barikoi::administrative()->getUnions('Dhamrai');
     * ```
     */
    public function getUnions(?string $subdistrict = null): array
    {
        $params = $subdistrict ? ['subdistrict' => $subdistrict] : [];
        return $this->client->get('/unions', $params);
    }

    /**
     * Get Areas
     *
     * Retrieve a list of areas/neighborhoods, optionally filtered by city.
     *
     * @param string|null $city Optional city name to filter areas
     * @return array Response containing array of areas
     *
     * @example
     * ```php
     * $areas = Barikoi::administrative()->getAreas('Dhaka');
     * ```
     */
    public function getAreas(?string $city = null): array
    {
        $params = $city ? ['city' => $city] : [];
        return $this->client->get('/areas', $params);
    }

    /**
     * Get Ward & Zone from LatLng
     *
     * Retrieve both ward and zone information for a given coordinate.
     * Wards are electoral and administrative divisions within city corporations.
     *
     * @param float $longitude The longitude coordinate
     * @param float $latitude The latitude coordinate
     * @return array Response containing ward and zone information
     *
     * @example
     * ```php
     * $wardZone = Barikoi::administrative()->getWardAndZone(90.3572, 23.8067);
     * ```
     */
    public function getWardAndZone(float $longitude, float $latitude): array
    {
        return $this->client->get('/ward-zone', [
            'longitude' => $longitude,
            'latitude' => $latitude,
        ]);
    }

    /**
     * Get Ward from LatLng
     *
     * Retrieve ward information for a given coordinate.
     *
     * @param float $longitude The longitude coordinate
     * @param float $latitude The latitude coordinate
     * @return array Response containing ward information
     *
     * @example
     * ```php
     * $ward = Barikoi::administrative()->getWard(90.3572, 23.8067);
     * ```
     */
    public function getWard(float $longitude, float $latitude): array
    {
        return $this->client->get('/ward', [
            'longitude' => $longitude,
            'latitude' => $latitude,
        ]);
    }

    /**
     * Get All Ward Geometry
     *
     * Retrieve geometry data (boundaries) for all wards. Useful for mapping
     * and visualization of ward boundaries.
     *
     * @return array Response containing geometry data for all wards
     *
     * @example
     * ```php
     * $allWards = Barikoi::administrative()->getAllWardGeometry();
     * ```
     */
    public function getAllWardGeometry(): array
    {
        return $this->client->get('/ward/geometry');
    }

    /**
     * Get Specific Ward Geometry
     *
     * Retrieve geometry data (boundaries) for a specific ward by ID.
     *
     * @param string $wardId The unique identifier of the ward
     * @return array Response containing geometry data for the specified ward
     *
     * @example
     * ```php
     * $wardGeometry = Barikoi::administrative()->getWardGeometry('ward_id');
     * ```
     */
    public function getWardGeometry(string $wardId): array
    {
        return $this->client->get("/ward/geometry/{$wardId}");
    }

    /**
     * Get All Zones
     *
     * Retrieve a list of all zones. Zones are administrative divisions
     * within city corporations.
     *
     * @return array Response containing array of all zones
     *
     * @example
     * ```php
     * $zones = Barikoi::administrative()->getAllZones();
     * ```
     */
    public function getAllZones(): array
    {
        return $this->client->get('/zones');
    }

    /**
     * Get Zone from LatLng
     *
     * Retrieve zone information for a given coordinate.
     *
     * @param float $longitude The longitude coordinate
     * @param float $latitude The latitude coordinate
     * @return array Response containing zone information
     *
     * @example
     * ```php
     * $zone = Barikoi::administrative()->getZone(90.3572, 23.8067);
     * ```
     */
    public function getZone(float $longitude, float $latitude): array
    {
        return $this->client->get('/zone', [
            'longitude' => $longitude,
            'latitude' => $latitude,
        ]);
    }

    /**
     * Get City Corporation by Geolocation
     *
     * Determine which city corporation (if any) a given coordinate falls within.
     * Includes information about Dhaka North and South City Corporations.
     *
     * @param float $longitude The longitude coordinate
     * @param float $latitude The latitude coordinate
     * @return array Response containing city corporation information
     *
     * @example
     * ```php
     * $cityCorp = Barikoi::administrative()->getCityCorporation(90.3572, 23.8067);
     * ```
     */
    public function getCityCorporation(float $longitude, float $latitude): array
    {
        return $this->client->get("/search/dncc/{$longitude}/{$latitude}");
    }
}


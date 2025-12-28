<?php

namespace Vendor\BarikoiApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Barikoi Facade - Easy access to Barikoi SDK
 *
 * Service Methods:
 * @method static \Vendor\BarikoiApi\Services\LocationService location()
 * @method static \Vendor\BarikoiApi\Services\RouteService route()
 * @method static \Vendor\BarikoiApi\Services\AdministrativeService administrative()
 * @method static \Vendor\BarikoiApi\Services\GeofenceService geofence()
 *
 * Location Methods (Direct Access):
 * @method static object reverseGeocode(float $longitude, float $latitude, array $options = [])
 * @method static object autocomplete(string $query, array $options = [])
 * @method static object searchPlace(string $query, array $options = [])
 * @method static object geocode(string $address, array $options = [])
 * @method static array placeDetails(string $placeCode, array $options = [])
 * @method static object snapToRoad(float $latitude, float $longitude)
 * @method static object nearby(float $longitude, float $latitude, float $distance = 0.5, int $limit = 10, array $options = [])
 * @method static object checkNearby(float $destinationLatitude,float $destinationLongitude,float $currentLatitude,float $currentLongitude,float $radius = 50)
 *
 * Route Methods (Direct Access):
 * @method static object routeOverview(array $points, array $options = [])
 * @method static object calculateRoute(array $startDestination, array $options = [])
 * @method static object detailedNavigation(float $startLat, float $startLng, float $destLat, float $destLng, array $options = [])
 *
 * @see \Vendor\BarikoiApi\Barikoi
 */
class Barikoi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'barikoi';
    }
}

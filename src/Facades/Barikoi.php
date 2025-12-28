<?php

namespace Barikoi\BarikoiApis\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Barikoi Facade - Easy access to Barikoi SDK
 *
 * Service Methods:
 * @method static \Barikoi\BarikoiApis\Services\LocationService location()
 * @method static \Barikoi\BarikoiApis\Services\RouteService route()
 * @method static \Barikoi\BarikoiApis\Services\AdministrativeService administrative()
 * @method static \Barikoi\BarikoiApis\Services\GeofenceService geofence()
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
 * @see \Barikoi\BarikoiApis\Barikoi
 */
class Barikoi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'barikoi';
    }
}

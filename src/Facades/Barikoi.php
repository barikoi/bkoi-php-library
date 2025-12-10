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
 * @method static array reverseGeocode(float $longitude, float $latitude, array $options = [])
 * @method static array autocomplete(string $query, array $options = [])
 * @method static array searchPlace(string $query, array $options = [])
 * @method static array geocode(string $address, array $options = [])
 * @method static array getPlaceDetails(string $placeCode, array $options = [])
 * @method static array snapToRoad(float $latitude, float $longitude)
 * @method static array nearby(float $longitude, float $latitude, float $distance = 0.5, int $limit = 10, array $options = [])
 * @method static array nearbyWithCategory(float $longitude, float $latitude, string $category, float $distance = 1.0, int $limit = 10)
 * @method static array nearbyWithTypes(float $longitude, float $latitude, array $types, float $distance = 5.0, int $limit = 5)
 * @method static array pointInPolygon(float $longitude, float $latitude, array $polygon)
 *
 * Route Methods (Direct Access):
 * @method static array detailedNavigation(float $startLat, float $startLng, float $destLat, float $destLng, array $options = [])
 * @method static array optimizedRoute(string $source, string $destination, array $waypoints = [], array $options = [])
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

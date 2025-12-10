<?php

namespace Vendor\PackageName\Tests\Unit;

use Vendor\PackageName\Tests\TestCase;
use Vendor\PackageName\Barikoi;
use Vendor\PackageName\Services\LocationService;
use Vendor\PackageName\Services\RouteService;
use Vendor\PackageName\Services\AdministrativeService;
use Vendor\PackageName\Services\GeofenceService;
use Illuminate\Support\Facades\Http;

class BarikoiIntegrationTest extends TestCase
{
    protected Barikoi $barikoi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->barikoi = new Barikoi('test-key', 'https://barikoi.xyz/v2/api');
    }

    // Test main class instantiation
    public function test_barikoi_class_instantiation()
    {
        $this->assertInstanceOf(Barikoi::class, $this->barikoi);
    }

    // Test location service is lazy loaded
    public function test_location_service_lazy_loading()
    {
        $location1 = $this->barikoi->location();
        $location2 = $this->barikoi->location();

        $this->assertInstanceOf(LocationService::class, $location1);
        // Should return same instance (lazy loaded)
        $this->assertSame($location1, $location2);
    }

    // Test route service is lazy loaded
    public function test_route_service_lazy_loading()
    {
        $route1 = $this->barikoi->route();
        $route2 = $this->barikoi->route();

        $this->assertInstanceOf(RouteService::class, $route1);
        $this->assertSame($route1, $route2);
    }

    // Test administrative service is lazy loaded
    public function test_administrative_service_lazy_loading()
    {
        $admin1 = $this->barikoi->administrative();
        $admin2 = $this->barikoi->administrative();

        $this->assertInstanceOf(AdministrativeService::class, $admin1);
        $this->assertSame($admin1, $admin2);
    }

    // Test geofence service is lazy loaded
    public function test_geofence_service_lazy_loading()
    {
        $geo1 = $this->barikoi->geofence();
        $geo2 = $this->barikoi->geofence();

        $this->assertInstanceOf(GeofenceService::class, $geo1);
        $this->assertSame($geo1, $geo2);
    }

    // Test direct reverse geocode method works
    public function test_direct_reverse_geocode_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->reverseGeocode(90.3572, 23.8067);

        $this->assertIsArray($result);
    }

    // Test direct autocomplete method works
    public function test_direct_autocomplete_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->autocomplete('Dhanmondi');

        $this->assertIsArray($result);
    }

    // Test direct geocode method works
    public function test_direct_geocode_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->geocode('Dhanmondi, Dhaka');

        $this->assertIsArray($result);
    }

    // Test direct searchPlace method works
    public function test_direct_search_place_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->searchPlace('restaurant');

        $this->assertIsArray($result);
    }

    // Test direct getPlaceDetails method works
    public function test_direct_get_place_details_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->getPlaceDetails('place-123');

        $this->assertIsArray($result);
    }

    // Test direct snapToRoad method works
    public function test_direct_snap_to_road_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->snapToRoad([
            ['longitude' => 90.3572, 'latitude' => 23.8067]
        ]);

        $this->assertIsArray($result);
    }

    // Test direct nearby method works
    public function test_direct_nearby_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->nearby(90.3572, 23.8067, 1000);

        $this->assertIsArray($result);
    }

    // Test direct nearbyWithCategory method works
    public function test_direct_nearby_with_category_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->nearbyWithCategory(90.3572, 23.8067, 'restaurant', 1000);

        $this->assertIsArray($result);
    }

    // Test direct nearbyWithTypes method works
    public function test_direct_nearby_with_types_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->nearbyWithTypes(90.3572, 23.8067, ['restaurant', 'hospital'], 1000);

        $this->assertIsArray($result);
    }

    // Test direct pointInPolygon method works
    public function test_direct_point_in_polygon_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $polygon = [
            ['longitude' => 90.35, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.81],
        ];

        $result = $this->barikoi->pointInPolygon(90.3572, 23.8067, $polygon);

        $this->assertIsArray($result);
    }

    // Test both direct and service methods return same result
    public function test_direct_and_service_methods_are_equivalent()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'data' => 'test'], 200)]);

        $directResult = $this->barikoi->reverseGeocode(90.3572, 23.8067);

        Http::fake(['*' => Http::response(['status' => 200, 'data' => 'test'], 200)]);

        $serviceResult = $this->barikoi->location()->reverseGeocode(90.3572, 23.8067);

        $this->assertEquals($directResult, $serviceResult);
    }

    // Test multiple service calls work correctly
    public function test_multiple_service_calls()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result1 = $this->barikoi->reverseGeocode(90.3572, 23.8067);
        $result2 = $this->barikoi->autocomplete('Dhaka');
        $result3 = $this->barikoi->searchPlace('restaurant');

        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertIsArray($result3);
    }

    // Test creating instance without API key uses config
    public function test_uses_config_for_api_key()
    {
        config(['barikoi.api_key' => 'from-config']);
        config(['barikoi.base_url' => 'https://from-config.com']);

        $barikoi = new Barikoi();

        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $barikoi->reverseGeocode(90.3572, 23.8067);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'from-config.com')
                && str_contains($request->url(), 'api_key=from-config');
        });
    }

    // Test creating instance with custom API key
    public function test_uses_custom_api_key()
    {
        $barikoi = new Barikoi('custom-key', 'https://custom-url.com');

        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $barikoi->reverseGeocode(90.3572, 23.8067);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'custom-url.com')
                && str_contains($request->url(), 'api_key=custom-key');
        });
    }

    // Test all services share same client
    public function test_all_services_share_same_client()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        // Call methods from different services
        $this->barikoi->reverseGeocode(90.3572, 23.8067);
        $this->barikoi->location()->autocomplete('Dhaka');

        // Both should use same API key from shared client
        Http::assertSentCount(2);
    }
}

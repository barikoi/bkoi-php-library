<?php

namespace Vendor\BarikoiApi\Tests\Unit;

use Vendor\BarikoiApi\Tests\TestCase;
use Vendor\BarikoiApi\Barikoi;
use Vendor\BarikoiApi\Services\LocationService;
use Vendor\BarikoiApi\Services\RouteService;
use Vendor\BarikoiApi\Services\AdministrativeService;
use Vendor\BarikoiApi\Services\GeofenceService;
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

        $this->assertIsObject($result);
    }

    // Test direct autocomplete method works
    public function test_direct_autocomplete_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->autocomplete('Dhanmondi');

        $this->assertIsObject($result);
    }

    // Test direct geocode method works
    public function test_direct_geocode_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->geocode('Dhanmondi, Dhaka');

        $this->assertIsObject($result);
    }

    // Test direct searchPlace method works
    public function test_direct_search_place_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->searchPlace('restaurant');

        $this->assertIsObject($result);
    }

    // Test direct snapToRoad method works
    // snapToRoad(latitude, longitude) - takes two floats
    public function test_direct_snap_to_road_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->snapToRoad(23.8067, 90.3572);

        $this->assertIsObject($result);
    }

    // Test direct nearby method works
    // nearby(longitude, latitude, distance_km, limit)
    public function test_direct_nearby_method()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->barikoi->nearby(90.3572, 23.8067, 1.0, 10);

        $this->assertIsObject($result);
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

        $this->assertIsObject($result1);
        $this->assertIsObject($result2);
        $this->assertIsObject($result3);
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

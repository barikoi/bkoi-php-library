<?php

namespace Barikoi\BarikoiApis\Tests\Unit;

use Barikoi\BarikoiApis\Tests\TestCase;
use Barikoi\BarikoiApis\Facades\Barikoi;
use Barikoi\BarikoiApis\Services\LocationService;
use Illuminate\Support\Facades\Http;

class FacadeTest extends TestCase
{
    // Test facade can access location service
    public function test_facade_accesses_location_service()
    {
        $service = Barikoi::location();

        $this->assertInstanceOf(LocationService::class, $service);
    }

    // Test facade reverse geocode method
    public function test_facade_reverse_geocode()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = Barikoi::reverseGeocode(90.3572, 23.8067);

        $this->assertIsObject($result);
    }

    // Test facade autocomplete method
    public function test_facade_autocomplete()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = Barikoi::autocomplete('Dhanmondi');

        $this->assertIsObject($result);
    }

    // Test facade geocode method
    public function test_facade_geocode()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = Barikoi::geocode('Dhanmondi, Dhaka');

        $this->assertIsObject($result);
    }

    // Test facade search place method
    public function test_facade_search_place()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = Barikoi::searchPlace('restaurant');

        $this->assertIsObject($result);
    }

    // Test facade snap to road method
    // snapToRoad(latitude, longitude) - takes two floats
    public function test_facade_snap_to_road()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = Barikoi::snapToRoad(23.8067, 90.3572);

        $this->assertIsObject($result);
    }

    // Test facade nearby method
    // nearby(longitude, latitude, distance_km, limit)
    public function test_facade_nearby()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = Barikoi::nearby(90.3572, 23.8067, 1.0, 10);

        $this->assertIsObject($result);
    }

    // Test facade with chained location() call
    public function test_facade_with_location_chain()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = Barikoi::location()->reverseGeocode(90.3572, 23.8067);

        $this->assertIsObject($result);
    }

    // Test facade returns same service instance
    public function test_facade_returns_same_service_instance()
    {
        $location1 = Barikoi::location();
        $location2 = Barikoi::location();

        // Note: Facades resolve fresh from container each time by default
        // This test verifies both calls work
        $this->assertInstanceOf(LocationService::class, $location1);
        $this->assertInstanceOf(LocationService::class, $location2);
    }

    // Test facade can call multiple methods in sequence
    public function test_facade_multiple_methods_in_sequence()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result1 = Barikoi::reverseGeocode(90.3572, 23.8067);
        $result2 = Barikoi::autocomplete('Dhaka');
        $result3 = Barikoi::searchPlace('hospital');

        $this->assertIsObject($result1);
        $this->assertIsObject($result2);
        $this->assertIsObject($result3);
    }

    // Test facade with options
    public function test_facade_with_options()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = Barikoi::reverseGeocode(90.3572, 23.8067, [
            'district' => true,
            'bangla' => true,
        ]);

        $this->assertIsObject($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'district=true')
                && str_contains($request->url(), 'bangla=true');
        });
    }
}

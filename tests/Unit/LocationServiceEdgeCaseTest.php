<?php

namespace Barikoi\BarikoiApis\Tests\Unit;

use Barikoi\BarikoiApis\Tests\TestCase;
use Barikoi\BarikoiApis\Services\LocationService;
use Barikoi\BarikoiApis\BarikoiClient;
use Illuminate\Support\Facades\Http;

class LocationServiceEdgeCaseTest extends TestCase
{
    protected LocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new BarikoiClient('test-key', 'https://barikoi.xyz/v2/api');
        $this->service = new LocationService($client);
    }

    // Test reverse geocode with negative coordinates
    public function test_reverse_geocode_with_negative_coordinates()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->reverseGeocode(-90.3572, -23.8067);

        $this->assertIsObject($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'longitude=-90.3572')
                && str_contains($request->url(), 'latitude=-23.8067');
        });
    }

    // Test reverse geocode with zero coordinates
    public function test_reverse_geocode_with_zero_coordinates()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->reverseGeocode(0, 0);

        $this->assertIsObject($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'longitude=0')
                && str_contains($request->url(), 'latitude=0');
        });
    }

    // Test reverse geocode with very precise coordinates (many decimals)
    public function test_reverse_geocode_with_precise_coordinates()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->reverseGeocode(90.35720123456, 23.80670987654);

        $this->assertIsObject($result);
    }

    // Test reverse geocode with mixed boolean and string options
    public function test_reverse_geocode_with_mixed_options()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->reverseGeocode(90.3572, 23.8067, [
            'district' => true,
            'bangla' => false,
            'custom_string' => 'value',
            'custom_number' => 100,
        ]);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'district=true')
                && str_contains($url, 'bangla=false')
                && str_contains($url, 'custom_string=value')
                && str_contains($url, 'custom_number=100');
        });
    }

    // Test autocomplete with empty string
    public function test_autocomplete_with_empty_string()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'places' => []], 200)]);

        $result = $this->service->autocomplete('');

        $this->assertIsObject($result);
    }

    // Test autocomplete with special characters
    public function test_autocomplete_with_special_characters()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->autocomplete('Dhanmondi & Road #27');

        $this->assertIsObject($result);
    }

    // Test autocomplete with unicode/bangla text
    public function test_autocomplete_with_bangla_text()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->autocomplete('ধানমন্ডি');

        $this->assertIsObject($result);
    }

    // Test geocode with very long address
    public function test_geocode_with_long_address()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $longAddress = 'House 123, Road 456, Block A, Section 789, Very Long Area Name, District Name, Division Name, Country, Postal Code 12345';
        $result = $this->service->geocode($longAddress);

        $this->assertIsObject($result);
    }

    // Test geocode with only city name
    public function test_geocode_with_minimal_address()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->geocode('Dhaka');

        $this->assertIsObject($result);
    }

    // Test nearby with zero distance
    // nearby(longitude, latitude, distance_km, limit)
    public function test_nearby_with_zero_distance()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->nearby(90.3572, 23.8067, 0, 10);

        $this->assertIsObject($result);
    }

    // Test nearby with very large distance
    // Distance is in km, so 100 = 100km, and is in the URL path
    public function test_nearby_with_large_distance()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->nearby(90.3572, 23.8067, 100, 10); // 100km

        $this->assertIsObject($result);
        Http::assertSent(function ($request) {
            // Distance is in URL path: /nearby/{distance}/{limit}
            return str_contains($request->url(), '/nearby/100/10');
        });
    }

    // Test snap to road with single point
    // snapToRoad(latitude, longitude) takes two floats, not an array
    public function test_snap_to_road_with_single_point()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->snapToRoad(23.8067, 90.3572);

        $this->assertIsObject($result);
    }

    // Test snap to road with precise coordinates
    public function test_snap_to_road_with_precise_coordinates()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->snapToRoad(23.806525320635505, 90.36129978225671);

        $this->assertIsObject($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'point=');
        });
    }

    // Test search place with limit option
    public function test_search_place_with_limit()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->searchPlace('restaurant', ['limit' => 5]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'limit=5');
        });
    }

    // Test search place with coordinates for proximity
    public function test_search_place_with_proximity_coordinates()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->searchPlace('restaurant', [
            'longitude' => 90.3572,
            'latitude' => 23.8067,
            'limit' => 10,
        ]);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'longitude=90.3572')
                && str_contains($url, 'latitude=23.8067')
                && str_contains($url, 'limit=10');
        });
    }

    // Test nearby with additional options
    // nearby(longitude, latitude, distance_km, limit, options)
    public function test_nearby_with_custom_options()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->nearby(90.3572, 23.8067, 1.0, 50, [
            'type' => 'restaurant',
        ]);

        Http::assertSent(function ($request) {
            $url = $request->url();
            // limit is in URL path, options like type are in query
            return str_contains($url, '/nearby/1/50')
                && str_contains($url, 'type=restaurant');
        });
    }
}

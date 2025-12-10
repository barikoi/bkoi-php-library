<?php

namespace Vendor\PackageName\Tests\Unit;

use Vendor\PackageName\Tests\TestCase;
use Vendor\PackageName\Services\LocationService;
use Vendor\PackageName\BarikoiClient;
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

        $this->assertIsArray($result);
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

        $this->assertIsArray($result);
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

        $this->assertIsArray($result);
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

        $this->assertIsArray($result);
    }

    // Test autocomplete with special characters
    public function test_autocomplete_with_special_characters()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->autocomplete('Dhanmondi & Road #27');

        $this->assertIsArray($result);
    }

    // Test autocomplete with unicode/bangla text
    public function test_autocomplete_with_bangla_text()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->autocomplete('ধানমন্ডি');

        $this->assertIsArray($result);
    }

    // Test geocode with very long address
    public function test_geocode_with_long_address()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $longAddress = 'House 123, Road 456, Block A, Section 789, Very Long Area Name, District Name, Division Name, Country, Postal Code 12345';
        $result = $this->service->geocode($longAddress);

        $this->assertIsArray($result);
    }

    // Test geocode with only city name
    public function test_geocode_with_minimal_address()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->geocode('Dhaka');

        $this->assertIsArray($result);
    }

    // Test nearby with zero distance
    public function test_nearby_with_zero_distance()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->nearby(90.3572, 23.8067, 0);

        $this->assertIsArray($result);
    }

    // Test nearby with very large distance
    public function test_nearby_with_large_distance()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->nearby(90.3572, 23.8067, 100000); // 100km

        $this->assertIsArray($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'distance=100000');
        });
    }

    // Test nearby with types - empty array
    public function test_nearby_with_empty_types_array()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->nearbyWithTypes(90.3572, 23.8067, [], 1000);

        $this->assertIsArray($result);
    }

    // Test nearby with types - single type
    public function test_nearby_with_single_type()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->nearbyWithTypes(90.3572, 23.8067, ['restaurant'], 1000);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'types=restaurant');
        });
    }

    // Test nearby with types - many types
    public function test_nearby_with_many_types()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $types = ['restaurant', 'hospital', 'pharmacy', 'atm', 'bank', 'school', 'hotel'];
        $result = $this->service->nearbyWithTypes(90.3572, 23.8067, $types, 1000);

        $this->assertIsArray($result);
    }

    // Test snap to road with single point
    public function test_snap_to_road_with_single_point()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->snapToRoad([
            ['longitude' => 90.3572, 'latitude' => 23.8067]
        ]);

        $this->assertIsArray($result);
    }

    // Test snap to road with many points
    public function test_snap_to_road_with_many_points()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $points = [];
        for ($i = 0; $i < 100; $i++) {
            $points[] = ['longitude' => 90.3572 + ($i * 0.001), 'latitude' => 23.8067 + ($i * 0.001)];
        }

        $result = $this->service->snapToRoad($points);

        $this->assertIsArray($result);
    }

    // Test snap to road with empty array
    public function test_snap_to_road_with_empty_array()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->snapToRoad([]);

        $this->assertIsArray($result);
    }

    // Test point in polygon with triangle (3 points)
    public function test_point_in_polygon_with_triangle()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $triangle = [
            ['longitude' => 90.35, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.80],
            ['longitude' => 90.355, 'latitude' => 23.81],
        ];

        $result = $this->service->pointInPolygon(90.3525, 23.805, $triangle);

        $this->assertIsArray($result);
    }

    // Test point in polygon with complex polygon (many points)
    public function test_point_in_polygon_with_complex_shape()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $complexPolygon = [
            ['longitude' => 90.35, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.80],
            ['longitude' => 90.365, 'latitude' => 23.805],
            ['longitude' => 90.36, 'latitude' => 23.81],
            ['longitude' => 90.355, 'latitude' => 23.815],
            ['longitude' => 90.35, 'latitude' => 23.81],
            ['longitude' => 90.345, 'latitude' => 23.805],
        ];

        $result = $this->service->pointInPolygon(90.355, 23.805, $complexPolygon);

        $this->assertIsArray($result);
    }

    // Test point in polygon at exact boundary
    public function test_point_in_polygon_on_boundary()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $polygon = [
            ['longitude' => 90.35, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.81],
            ['longitude' => 90.35, 'latitude' => 23.81],
        ];

        // Point exactly on the edge
        $result = $this->service->pointInPolygon(90.35, 23.80, $polygon);

        $this->assertIsArray($result);
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

    // Test get place details with special characters in ID
    public function test_get_place_details_with_special_id()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->getPlaceDetails('place-id-123-abc-xyz');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/place/place-id-123-abc-xyz');
        });
    }

    // Test nearby with additional options
    public function test_nearby_with_custom_options()
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        $result = $this->service->nearby(90.3572, 23.8067, 1000, [
            'limit' => 50,
            'type' => 'restaurant',
        ]);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'limit=50')
                && str_contains($url, 'type=restaurant');
        });
    }
}

<?php

namespace Vendor\PackageName\Tests\Feature;

use Vendor\PackageName\Tests\TestCase;
use Vendor\PackageName\Facades\Barikoi;

class BarikoiLocationTest extends TestCase
{
    // Test reverse geocoding with location()->
    public function test_reverse_geocoding()
    {
        $result = Barikoi::location()->reverseGeocode(90.3572, 23.8067, [
            'district' => true,
            'bangla' => true,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    // Test reverse geocoding direct call (without location()->)
    public function test_reverse_geocoding_direct()
    {
        $result = Barikoi::reverseGeocode(90.3572, 23.8067, [
            'district' => true,
            'post_code' => true,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    // Test reverse geocoding with all options
    public function test_reverse_geocoding_with_all_options()
    {
        $result = Barikoi::reverseGeocode(90.3572, 23.8067, [
            'district' => true,
            'post_code' => true,
            'country' => true,
            'sub_district' => true,
            'union' => true,
            'pauroshova' => true,
            'location_type' => true,
            'division' => true,
            'address' => true,
            'area' => true,
            'bangla' => true,
            'thana' => true,
        ]);

        $this->assertIsArray($result);
    }

    // Test autocomplete
    public function test_autocomplete()
    {
        $result = Barikoi::location()->autocomplete('Dhanmondi');

        $this->assertIsArray($result);
    }

    // Test autocomplete direct call
    public function test_autocomplete_direct()
    {
        $result = Barikoi::autocomplete('Gulshan');

        $this->assertIsArray($result);
    }

    // Test geocoding
    public function test_geocode()
    {
        $result = Barikoi::location()->geocode('Dhanmondi 27, Dhaka');

        $this->assertIsArray($result);
    }

    // Test geocoding direct call
    public function test_geocode_direct()
    {
        $result = Barikoi::geocode('Banani, Dhaka');

        $this->assertIsArray($result);
    }

    // Test search place
    public function test_search_place()
    {
        $result = Barikoi::location()->searchPlace('restaurant');

        $this->assertIsArray($result);
    }

    // Test search place direct call
    public function test_search_place_direct()
    {
        $result = Barikoi::searchPlace('hospital');

        $this->assertIsArray($result);
    }

    // Test search place with options
    public function test_search_place_with_options()
    {
        $result = Barikoi::searchPlace('restaurant', [
            'limit' => 10,
            'longitude' => 90.3572,
            'latitude' => 23.8067,
        ]);

        $this->assertIsArray($result);
    }

    // Test get place details
    public function test_get_place_details()
    {
        $result = Barikoi::getPlaceDetails('test-place-id');

        $this->assertIsArray($result);
    }

    // Test nearby places
    public function test_nearby()
    {
        $result = Barikoi::location()->nearby(90.3572, 23.8067, 1000);

        $this->assertIsArray($result);
    }

    // Test nearby direct call
    public function test_nearby_direct()
    {
        $result = Barikoi::nearby(90.3572, 23.8067, 2000);

        $this->assertIsArray($result);
    }

    // Test nearby with options
    public function test_nearby_with_options()
    {
        $result = Barikoi::nearby(90.3572, 23.8067, 1000, [
            'limit' => 20,
        ]);

        $this->assertIsArray($result);
    }

    // Test nearby with category
    public function test_nearby_with_category()
    {
        $result = Barikoi::nearbyWithCategory(90.3572, 23.8067, 'restaurant', 1000);

        $this->assertIsArray($result);
    }

    // Test nearby with multiple types
    public function test_nearby_with_types()
    {
        $result = Barikoi::nearbyWithTypes(90.3572, 23.8067, [
            'restaurant',
            'hospital',
            'pharmacy',
        ], 1000);

        $this->assertIsArray($result);
    }

    // Test snap to road
    public function test_snap_to_road()
    {
        $points = [
            ['longitude' => 90.3572, 'latitude' => 23.8067],
            ['longitude' => 90.3580, 'latitude' => 23.8070],
        ];

        $result = Barikoi::location()->snapToRoad($points);

        $this->assertIsArray($result);
    }

    // Test snap to road direct call
    public function test_snap_to_road_direct()
    {
        $points = [
            ['longitude' => 90.3572, 'latitude' => 23.8067],
            ['longitude' => 90.3580, 'latitude' => 23.8070],
            ['longitude' => 90.3585, 'latitude' => 23.8075],
        ];

        $result = Barikoi::snapToRoad($points);

        $this->assertIsArray($result);
    }

    // Test point in polygon
    public function test_point_in_polygon()
    {
        $polygon = [
            ['longitude' => 90.35, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.81],
            ['longitude' => 90.35, 'latitude' => 23.81],
        ];

        $result = Barikoi::location()->pointInPolygon(90.3572, 23.8067, $polygon);

        $this->assertIsArray($result);
    }

    // Test point in polygon direct call
    public function test_point_in_polygon_direct()
    {
        $polygon = [
            ['longitude' => 90.35, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.81],
            ['longitude' => 90.35, 'latitude' => 23.81],
        ];

        $result = Barikoi::pointInPolygon(90.3572, 23.8067, $polygon);

        $this->assertIsArray($result);
    }

    // Test point outside polygon
    public function test_point_outside_polygon()
    {
        $polygon = [
            ['longitude' => 90.35, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.81],
            ['longitude' => 90.35, 'latitude' => 23.81],
        ];

        // Point clearly outside the polygon
        $result = Barikoi::pointInPolygon(91.0000, 24.0000, $polygon);

        $this->assertIsArray($result);
    }
}

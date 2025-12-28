<?php

namespace Barikoi\BarikoiApis\Tests\Feature;

use Barikoi\BarikoiApis\Tests\TestCase;
use Barikoi\BarikoiApis\Facades\Barikoi;
use Illuminate\Support\Facades\Http;

class BarikoiLocationTest extends TestCase
{
    // Test reverse geocoding with location()->
    public function test_reverse_geocoding()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'place' => []], 200)]);

        $result = Barikoi::location()->reverseGeocode(90.3572, 23.8067, [
            'district' => true,
            'bangla' => true,
        ]);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('status', $result);
    }

    // Test reverse geocoding direct call (without location()->)
    public function test_reverse_geocoding_direct()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'place' => []], 200)]);

        $result = Barikoi::reverseGeocode(90.3572, 23.8067, [
            'district' => true,
            'post_code' => true,
        ]);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('status', $result);
    }

    // Test reverse geocoding with all options
    public function test_reverse_geocoding_with_all_options()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'place' => []], 200)]);

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

        $this->assertIsObject($result);
    }

    // Test autocomplete
    public function test_autocomplete()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'places' => []], 200)]);

        $result = Barikoi::location()->autocomplete('Dhanmondi');

        $this->assertIsObject($result);
    }

    // Test autocomplete direct call
    public function test_autocomplete_direct()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'places' => []], 200)]);

        $result = Barikoi::autocomplete('Gulshan');

        $this->assertIsObject($result);
    }

    // Test geocoding
    public function test_geocode()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'geocoded_address' => []], 200)]);

        $result = Barikoi::location()->geocode('Dhanmondi 27, Dhaka');

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('status', $result);
    }

    // Test geocoding direct call
    public function test_geocode_direct()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'geocoded_address' => []], 200)]);

        $result = Barikoi::geocode('Banani, Dhaka');

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('status', $result);
    }

    // Test geocoding with options
    public function test_geocode_with_options()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'geocoded_address' => []], 200)]);

        $result = Barikoi::geocode('shawrapara', [
            'thana' => true,
            'district' => true,
            'bangla' => true,
        ]);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('status', $result);
    }

    // Test search place
    public function test_search_place()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'places' => []], 200)]);

        $result = Barikoi::location()->searchPlace('restaurant');

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('places', $result);
    }

    // Test search place direct call
    public function test_search_place_direct()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'places' => []], 200)]);

        $result = Barikoi::searchPlace('hospital');

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('places', $result);
    }

    // Test search place with options
    public function test_search_place_with_options()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'places' => []], 200)]);

        $result = Barikoi::searchPlace('restaurant', [
            'limit' => 10,
            'longitude' => 90.3572,
            'latitude' => 23.8067,
        ]);

        $this->assertIsObject($result);
    }

    // Test nearby places
    // nearby(longitude, latitude, distance_km, limit)
    public function test_nearby()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'places' => []], 200)]);

        $result = Barikoi::location()->nearby(90.3572, 23.8067, 1.0, 10);

        $this->assertIsObject($result);
    }

    // Test nearby direct call
    public function test_nearby_direct()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'places' => []], 200)]);

        $result = Barikoi::nearby(90.3572, 23.8067, 2.0, 10);

        $this->assertIsObject($result);
    }

    // Test nearby with options
    public function test_nearby_with_options()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'places' => []], 200)]);

        $result = Barikoi::nearby(90.3572, 23.8067, 1.0, 20);

        $this->assertIsObject($result);
    }

    // Test snap to road
    // snapToRoad(latitude, longitude) - takes single point as two floats
    public function test_snap_to_road()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'snapped_point' => []], 200)]);

        $result = Barikoi::location()->snapToRoad(23.8067, 90.3572);

        $this->assertIsObject($result);
    }

    // Test snap to road direct call
    public function test_snap_to_road_direct()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'snapped_point' => []], 200)]);

        $result = Barikoi::snapToRoad(23.8067, 90.3572);

        $this->assertIsObject($result);
    }

}

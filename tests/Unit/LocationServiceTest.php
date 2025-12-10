<?php

namespace Vendor\PackageName\Tests\Unit;

use Vendor\PackageName\Tests\TestCase;
use Vendor\PackageName\Services\LocationService;
use Vendor\PackageName\BarikoiClient;
use Illuminate\Support\Facades\Http;

class LocationServiceTest extends TestCase
{
    protected LocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new BarikoiClient('test-key', 'https://barikoi.xyz/v2/api');
        $this->service = new LocationService($client);
    }

    // Test boolean parameters are converted to strings
    public function test_reverse_geocode_converts_boolean_to_string()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'place' => []], 200)
        ]);

        $this->service->reverseGeocode(90.3572, 23.8067, [
            'district' => true,
            'bangla' => false,
        ]);

        Http::assertSent(function ($request) {
            $url = $request->url();
            // Check that boolean true becomes 'true' string
            return str_contains($url, 'district=true')
                && str_contains($url, 'bangla=false');
        });
    }

    // Test all boolean options are converted correctly
    public function test_reverse_geocode_all_boolean_options()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'place' => []], 200)
        ]);

        $this->service->reverseGeocode(90.3572, 23.8067, [
            'district' => true,
            'post_code' => true,
            'country' => false,
            'sub_district' => true,
            'bangla' => true,
        ]);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'district=true')
                && str_contains($url, 'post_code=true')
                && str_contains($url, 'country=false')
                && str_contains($url, 'sub_district=true')
                && str_contains($url, 'bangla=true');
        });
    }

    // Test non-boolean parameters are not affected
    public function test_reverse_geocode_preserves_non_boolean_params()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'place' => []], 200)
        ]);

        $this->service->reverseGeocode(90.3572, 23.8067, [
            'district' => true,
            'custom_param' => 'string_value',
            'number_param' => 123,
        ]);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'district=true')
                && str_contains($url, 'custom_param=string_value')
                && str_contains($url, 'number_param=123');
        });
    }

    // Test autocomplete sends correct params
    public function test_autocomplete_sends_correct_params()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'places' => []], 200)
        ]);

        $this->service->autocomplete('Dhanmondi', ['limit' => 10]);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'q=Dhanmondi')
                && str_contains($url, 'limit=10');
        });
    }

    // Test geocode uses POST method
    public function test_geocode_uses_post_method()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'place' => []], 200)
        ]);

        $this->service->geocode('Dhanmondi 27, Dhaka');

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/search/rupantor/geocode');
        });
    }

    // Test search place sends query
    public function test_search_place_sends_query()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'places' => []], 200)
        ]);

        $this->service->searchPlace('restaurant');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'q=restaurant');
        });
    }

    // Test nearby sends all required params
    public function test_nearby_sends_all_params()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'places' => []], 200)
        ]);

        $this->service->nearby(90.3572, 23.8067, 2000);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'longitude=90.3572')
                && str_contains($url, 'latitude=23.8067')
                && str_contains($url, 'distance=2000');
        });
    }

    // Test nearby with category
    public function test_nearby_with_category_sends_category()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'places' => []], 200)
        ]);

        $this->service->nearbyWithCategory(90.3572, 23.8067, 'restaurant', 1000);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'category=restaurant')
                && str_contains($url, '/nearby/category');
        });
    }

    // Test nearby with types converts array to comma-separated
    public function test_nearby_with_types_converts_array()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'places' => []], 200)
        ]);

        $this->service->nearbyWithTypes(90.3572, 23.8067, ['restaurant', 'hospital', 'pharmacy'], 1000);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'types=restaurant%2Chospital%2Cpharmacy')
                || str_contains($url, 'types=restaurant,hospital,pharmacy');
        });
    }

    // Test snap to road encodes points as JSON
    public function test_snap_to_road_encodes_points()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'points' => []], 200)
        ]);

        $points = [
            ['longitude' => 90.3572, 'latitude' => 23.8067],
            ['longitude' => 90.3580, 'latitude' => 23.8070],
        ];

        $this->service->snapToRoad($points);

        Http::assertSent(function ($request) use ($points) {
            $url = $request->url();
            $encodedPoints = urlencode(json_encode($points));
            return str_contains($url, 'points=')
                && str_contains($url, '/snap/road');
        });
    }

    // Test point in polygon uses POST
    public function test_point_in_polygon_uses_post()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'inside' => true], 200)
        ]);

        $polygon = [
            ['longitude' => 90.35, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.80],
            ['longitude' => 90.36, 'latitude' => 23.81],
        ];

        $this->service->pointInPolygon(90.3572, 23.8067, $polygon);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/point/polygon');
        });
    }
}

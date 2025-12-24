<?php

namespace Vendor\BarikoiApi\Tests\Unit;

use Vendor\BarikoiApi\Tests\TestCase;
use Vendor\BarikoiApi\Services\LocationService;
use Vendor\BarikoiApi\BarikoiClient;
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

    // Test autocomplete sends correct params with allowed option
    public function test_autocomplete_sends_correct_params_with_bangla_option()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'places' => []], 200)
        ]);

        $this->service->autocomplete('Dhanmondi', ['bangla' => true]);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'q=Dhanmondi')
                && str_contains($url, 'bangla=1');
        });
    }

    // Test autocomplete throws validation exception for unsupported options
    public function test_autocomplete_throws_for_unsupported_options()
    {
        $this->expectException(\Vendor\BarikoiApi\Exceptions\BarikoiValidationException::class);

        $this->service->autocomplete('Dhanmondi', [
            'limit' => 10,
            'latitude' => 23.8,
        ]);
    }

    // Test reverse geocode throws validation exception for invalid latitude/longitude
    public function test_reverse_geocode_throws_for_invalid_coordinates()
    {
        $this->expectException(\Vendor\BarikoiApi\Exceptions\BarikoiValidationException::class);

        // Latitude > 90 and longitude > 180 should trigger validation error
        $this->service->reverseGeocode(181.0, 91.0);
    }

    // Test geocode throws validation exception when options are provided
    public function test_geocode_throws_for_unsupported_options()
    {
        $this->expectException(\Vendor\BarikoiApi\Exceptions\BarikoiValidationException::class);

        $this->service->geocode('shawrapara', [
            'thana' => true,
        ]);
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
                && str_contains($request->url(), '/search/rupantor/geocode')
                && str_contains(urldecode($request->body()), 'q=Dhanmondi 27, Dhaka');
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
    // nearby() creates its own client with base URL https://barikoi.xyz
    // and uses distance/limit in URL path: /v2/api/search/nearby/{distance}/{limit}
    public function test_nearby_sends_all_params()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'places' => []], 200)
        ]);

        // nearby(longitude, latitude, distance_km, limit)
        $this->service->nearby(90.3572, 23.8067, 0.5, 10);

        Http::assertSent(function ($request) {
            $url = $request->url();
            // Distance and limit are in URL path, not query params
            return str_contains($url, 'longitude=90.3572')
                && str_contains($url, 'latitude=23.8067')
                && str_contains($url, '/nearby/0.5/10');
        });
    }

    // Test snap to road accepts single point coordinates
    // snapToRoad(latitude, longitude) - note: latitude first!
    public function test_snap_to_road_sends_point()
    {
        Http::fake([
            '*' => Http::response(['status' => 200, 'points' => []], 200)
        ]);

        // snapToRoad(latitude, longitude) sends as "lat,lng" in 'point' param
        $this->service->snapToRoad(23.8067, 90.3572);

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'point=23.8067%2C90.3572')
                || str_contains($url, 'point=23.8067,90.3572');
        });
    }



}

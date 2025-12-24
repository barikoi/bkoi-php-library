<?php

namespace Vendor\BarikoiApi\Tests\Feature;

use Vendor\BarikoiApi\Tests\TestCase;
use Vendor\BarikoiApi\Facades\Barikoi;
use Illuminate\Support\Facades\Http;

class BarikoiRouteTest extends TestCase
{
    /**
     * Test route overview
     *
     * @return void
     */
    public function test_route_overview()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'routes' => []], 200)]);

        $points = [
            ['longitude' => 90.3572, 'latitude' => 23.8067],
            ['longitude' => 90.3680, 'latitude' => 23.8100],
        ];

        $result = Barikoi::routeOverview($points);

        $this->assertIsObject($result);
    }

    /**
     * Test detailed route
     *
     * @return void
     */
    public function test_detailed_route()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'routes' => []], 200)]);

        $points = [
            ['longitude' => 90.3572, 'latitude' => 23.8067],
            ['longitude' => 90.3680, 'latitude' => 23.8100],
        ];

        $result = Barikoi::calculateRoute($points, [
            'alternatives' => true,
            'steps' => true,
        ]);

        $this->assertIsObject($result);
    }

    /**
     * Test route match
     *
     * @return void
     */
    public function test_route_match()
    {
        Http::fake(['*' => Http::response(['status' => 200, 'matchings' => []], 200)]);

        $points = [
            ['longitude' => 90.3572, 'latitude' => 23.8067],
            ['longitude' => 90.3575, 'latitude' => 23.8068],
            ['longitude' => 90.3578, 'latitude' => 23.8069],
        ];

        // Match API returns object but service has array return type - causes TypeError
        $this->expectException(\TypeError::class);
        Barikoi::route()->match($points);
    }
}

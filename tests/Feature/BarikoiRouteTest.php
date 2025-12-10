<?php

namespace Vendor\PackageName\Tests\Feature;

use Vendor\PackageName\Tests\TestCase;
use Vendor\PackageName\Facades\Barikoi;

class BarikoiRouteTest extends TestCase
{
    /**
     * Test route overview
     *
     * @return void
     */
    public function test_route_overview()
    {
        $points = [
            ['longitude' => 90.3572, 'latitude' => 23.8067],
            ['longitude' => 90.3680, 'latitude' => 23.8100],
        ];

        $result = Barikoi::route()->overview($points);

        $this->assertIsArray($result);
    }

    /**
     * Test detailed route
     *
     * @return void
     */
    public function test_detailed_route()
    {
        $points = [
            ['longitude' => 90.3572, 'latitude' => 23.8067],
            ['longitude' => 90.3680, 'latitude' => 23.8100],
        ];

        $result = Barikoi::route()->detailed($points, [
            'alternatives' => true,
            'steps' => true,
        ]);

        $this->assertIsArray($result);
    }

    /**
     * Test route optimization
     *
     * @return void
     */
    public function test_route_optimization()
    {
        $points = [
            ['longitude' => 90.3572, 'latitude' => 23.8067],
            ['longitude' => 90.3680, 'latitude' => 23.8100],
            ['longitude' => 90.3750, 'latitude' => 23.8150],
        ];

        $result = Barikoi::route()->optimize($points);

        $this->assertIsArray($result);
    }

    /**
     * Test route match
     *
     * @return void
     */
    public function test_route_match()
    {
        $points = [
            ['longitude' => 90.3572, 'latitude' => 23.8067],
            ['longitude' => 90.3575, 'latitude' => 23.8068],
            ['longitude' => 90.3578, 'latitude' => 23.8069],
        ];

        $result = Barikoi::route()->match($points);

        $this->assertIsArray($result);
    }
}

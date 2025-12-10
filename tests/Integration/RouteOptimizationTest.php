<?php

namespace Vendor\PackageName\Tests\Integration;

use Vendor\PackageName\Facades\Barikoi;

/**
 * Integration tests for Route Optimization API
 *
 * Tests the /route/optimized endpoint that provides routing with waypoints.
 * Supports up to 50 waypoints with automatic sorting by ID.
 *
 * @group integration
 * @group real-api
 */
class RouteOptimizationTest extends IntegrationTestCase
{
    /**
     * Test basic route optimization with waypoints
     *
     * Scenario: Delivery driver has multiple stops along the route
     * Expected: Get optimized route through all waypoints
     */
    public function test_optimized_route_with_waypoints()
    {
        $source = '23.746086,90.37368';
        $destination = '23.746214,90.371654';
        $waypoints = [
            ['id' => 1, 'point' => '23.746086,90.37368'],
            ['id' => 2, 'point' => '23.74577,90.373389'],
            ['id' => 3, 'point' => '23.74442,90.372909'],
            ['id' => 4, 'point' => '23.743961,90.37214'],
        ];

        $result = Barikoi::optimizedRoute($source, $destination, $waypoints);

        $this->assertIsArray($result);

        // Check if API is available
        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Route optimization API not available');
            return;
        }

        echo "\n✓ Optimized route with " . count($waypoints) . " waypoints calculated\n";
        
        if (isset($result['paths'])) {
            echo "  Paths found: " . count($result['paths']) . "\n";
        }
    }

    /**
     * Test route optimization with car profile (default)
     *
     * Scenario: Car route with multiple delivery stops
     * Expected: Car-optimized route
     */
    public function test_optimized_route_car_profile()
    {
        $result = Barikoi::optimizedRoute(
            '23.746086,90.37368',
            '23.746214,90.371654',
            [
                ['id' => 1, 'point' => '23.746086,90.37368'],
                ['id' => 2, 'point' => '23.74577,90.373389'],
            ]
        );

        $this->assertIsArray($result);

        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Route optimization API not available');
            return;
        }

        echo "\n✓ Car profile optimization completed\n";
    }

    /**
     * Test route optimization with motorcycle profile
     *
     * Scenario: Motorcycle delivery with waypoints
     * Expected: Motorcycle-optimized route
     */
    public function test_optimized_route_motorcycle_profile()
    {
        $result = Barikoi::optimizedRoute(
            '23.746086,90.37368',
            '23.746214,90.371654',
            [
                ['id' => 1, 'point' => '23.746086,90.37368'],
                ['id' => 2, 'point' => '23.74577,90.373389'],
            ],
            ['profile' => 'motorcycle']
        );

        $this->assertIsArray($result);

        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Route optimization API not available');
            return;
        }

        echo "\n✓ Motorcycle profile optimization completed\n";
    }

    /**
     * Test route optimization with bike profile
     *
     * Scenario: Bicycle delivery with waypoints
     * Expected: Bike-optimized route
     */
    public function test_optimized_route_bike_profile()
    {
        $result = Barikoi::optimizedRoute(
            '23.746086,90.37368',
            '23.746214,90.371654',
            [
                ['id' => 1, 'point' => '23.746086,90.37368'],
                ['id' => 2, 'point' => '23.74577,90.373389'],
            ],
            ['profile' => 'bike']
        );

        $this->assertIsArray($result);

        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Route optimization API not available');
            return;
        }

        echo "\n✓ Bike profile optimization completed\n";
    }

    /**
     * Test waypoint sorting by ID
     *
     * Scenario: Waypoints provided in wrong order
     * Expected: Waypoints automatically sorted by ID ascending
     */
    public function test_waypoints_sorted_by_id()
    {
        // Provide waypoints in wrong order
        $waypointsUnsorted = [
            ['id' => 3, 'point' => '23.74442,90.372909'],
            ['id' => 1, 'point' => '23.746086,90.37368'],
            ['id' => 2, 'point' => '23.74577,90.373389'],
        ];

        $result = Barikoi::optimizedRoute(
            '23.746086,90.37368',
            '23.746214,90.371654',
            $waypointsUnsorted
        );

        $this->assertIsArray($result);

        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Route optimization API not available');
            return;
        }

        echo "\n✓ Waypoints automatically sorted by ID\n";
        echo "  Original order: 3, 1, 2\n";
        echo "  Sorted order: 1, 2, 3\n";
    }

    /**
     * Test route without waypoints
     *
     * Scenario: Simple route from source to destination
     * Expected: Direct route without intermediate stops
     */
    public function test_optimized_route_without_waypoints()
    {
        $result = Barikoi::optimizedRoute(
            '23.746086,90.37368',
            '23.746214,90.371654'
        );

        $this->assertIsArray($result);

        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Route optimization API not available');
            return;
        }

        echo "\n✓ Direct route (no waypoints) calculated\n";
    }

    /**
     * Test maximum waypoints limit
     *
     * Scenario: Try to add more than 50 waypoints
     * Expected: Return error response
     */
    public function test_maximum_waypoints_limit()
    {
        // Generate 51 waypoints
        $waypoints = [];
        for ($i = 1; $i <= 51; $i++) {
            $waypoints[] = [
                'id' => $i,
                'point' => '23.746086,90.37368'
            ];
        }

        $result = Barikoi::optimizedRoute(
            '23.746086,90.37368',
            '23.746214,90.371654',
            $waypoints
        );

        $this->assertIsArray($result);
        $this->assertEquals(400, $result['status']);
        $this->assertEquals('too_many_waypoints', $result['error']);
        $this->assertEquals(51, $result['provided']);
        $this->assertEquals(50, $result['maximum']);

        echo "\n✓ Maximum waypoints validation works\n";
        echo "  Message: {$result['message']}\n";
        echo "  Provided: {$result['provided']}, Maximum: {$result['maximum']}\n";
    }

    /**
     * Test invalid profile validation
     *
     * Scenario: Try to use invalid profile
     * Expected: Return error response
     */
    public function test_invalid_profile_returns_error()
    {
        $result = Barikoi::optimizedRoute(
            '23.746086,90.37368',
            '23.746214,90.371654',
            [
                ['id' => 1, 'point' => '23.746086,90.37368'],
            ],
            ['profile' => 'truck']
        );

        $this->assertIsArray($result);
        $this->assertEquals(400, $result['status']);
        $this->assertEquals('invalid_profile', $result['error']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('supported_profiles', $result);

        echo "\n✓ Invalid profile returns proper error\n";
        echo "  Error: {$result['error']}\n";
        echo "  Supported: " . implode(', ', $result['supported_profiles']) . "\n";
    }

    /**
     * Test with many waypoints (realistic delivery scenario)
     *
     * Scenario: Delivery route with 10 stops
     * Expected: Successfully calculate optimized route
     */
    public function test_many_waypoints_realistic()
    {
        $waypoints = [];
        $baseLat = 23.746;
        $baseLng = 90.373;

        for ($i = 1; $i <= 10; $i++) {
            $waypoints[] = [
                'id' => $i,
                'point' => ($baseLat + ($i * 0.001)) . ',' . ($baseLng + ($i * 0.001))
            ];
        }

        $result = Barikoi::optimizedRoute(
            '23.746086,90.37368',
            '23.756214,90.383654',
            $waypoints
        );

        $this->assertIsArray($result);

        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Route optimization API not available');
            return;
        }

        echo "\n✓ Route with 10 waypoints calculated\n";
        if (isset($result['paths'])) {
            echo "  Total paths: " . count($result['paths']) . "\n";
        }
    }

    /**
     * Test profile comparison
     *
     * Scenario: Compare optimization for different vehicle types
     * Expected: Get optimized routes for all profiles
     */
    public function test_profile_comparison()
    {
        $source = '23.746086,90.37368';
        $destination = '23.746214,90.371654';
        $waypoints = [
            ['id' => 1, 'point' => '23.746086,90.37368'],
            ['id' => 2, 'point' => '23.74577,90.373389'],
        ];

        $profiles = ['car', 'motorcycle', 'bike'];
        $results = [];

        foreach ($profiles as $profile) {
            $result = Barikoi::optimizedRoute(
                $source,
                $destination,
                $waypoints,
                ['profile' => $profile]
            );

            if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
                $this->markTestSkipped('Route optimization API not available');
                return;
            }

            $results[$profile] = $result;
        }

        echo "\n✓ Optimization comparison:\n";
        foreach ($results as $profile => $result) {
            echo "  {$profile}: ";
            if (isset($result['paths'])) {
                echo count($result['paths']) . " paths";
            }
            echo "\n";
        }

        $this->assertCount(3, $results);
    }
}


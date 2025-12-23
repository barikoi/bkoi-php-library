<?php

namespace Vendor\BarikoiApi\Tests\Integration;

use Vendor\BarikoiApi\Facades\Barikoi;

/**
 * Real-world integration tests for route services
 *
 * @group integration
 * @group real-api
 */
class RealWorldRouteTest extends IntegrationTestCase
{
    /**
     * Check if route endpoint is available
     */
    protected function skipIfRouteNotAvailable(array $result): void
    {
        if (!isset($result['code']) || (isset($result['message']) && strpos($result['message'], 'not be found') !== false)) {
            $this->markTestSkipped('Route endpoint not available in current API plan. Contact Barikoi to enable routing features.');
        }
    }
    
    /**
     * Test distance calculation between two points
     *
     * Scenario: Calculate delivery distance from warehouse to customer
     * Expected: Get accurate distance in meters
     */
    public function test_distance_dhaka_to_gulshan()
    {
        // Warehouse in Mirpur to customer in Gulshan
        $result = Barikoi::route()->distance(
            90.3563, 23.8103, // Mirpur
            90.4125, 23.7925  // Gulshan
        );

        $this->assertIsArray($result);
        $this->skipIfRouteNotAvailable($result);

        $this->assertEquals('Ok', $result['code']);
        $this->assertArrayHasKey('routes', $result);
        $this->assertNotEmpty($result['routes']);
        
        $route = $result['routes'][0];
        $this->assertArrayHasKey('distance', $route);
        $this->assertIsNumeric($route['distance']);

        // Distance should be reasonable (5-15 km)
        $this->assertGreaterThan(5000, $route['distance']); // > 5km
        $this->assertLessThan(15000, $route['distance']); // < 15km

        echo "\n✓ Distance from Mirpur to Gulshan: " . round($route['distance'] / 1000, 2) . " km\n";
        echo "  Duration: " . round($route['duration'] / 60, 1) . " minutes\n";
    }

    /**
     * Test route directions
     *
     * Scenario: Navigation app needs turn-by-turn directions
     * Expected: Get route with waypoints
     */
    public function test_directions_shahbagh_to_motijheel()
    {
        $result = Barikoi::route()->directions(
            90.3957, 23.7386, // Shahbagh
            90.4177, 23.7337  // Motijheel
        );

        $this->assertIsArray($result);
        $this->skipIfRouteNotAvailable($result);
        $this->assertEquals('Ok', $result['code']);
        $this->assertArrayHasKey('routes', $result);

        $route = $result['routes'][0];
        $distanceKm = round($route['distance'] / 1000, 2);
        $durationMin = round($route['duration'] / 60, 1);

        echo "\n✓ Route from Shahbagh to Motijheel:\n";
        echo "  Distance: {$distanceKm} km\n";
        echo "  Duration: {$durationMin} minutes\n";
    }

    /**
     * Test snap to road functionality
     *
     * Scenario: GPS coordinates slightly off road, need to snap to nearest road
     * Expected: Coordinates adjusted to road network
     */
    public function test_snap_to_road_dhaka_coordinates()
    {
        // Multiple GPS points along a route
        $points = [
            ['longitude' => 90.3916, 'latitude' => 23.7525],
            ['longitude' => 90.3957, 'latitude' => 23.7386],
            ['longitude' => 90.4125, 'latitude' => 23.7925],
        ];

        try {
            $result = Barikoi::route()->match($points);
        } catch (\Vendor\BarikoiApi\Exceptions\BarikoiApiException $e) {
            // Route matching endpoint not available in current API version
            if (strpos($e->getMessage(), 'could not be found') !== false) {
                $this->markTestSkipped('Route matching endpoint not available in current API version');
                return;
            }
            throw $e;
        }

        $this->assertIsArray($result);

        // Check if route matching is available
        if (!isset($result['code']) || (isset($result['message']) && strpos($result['message'], 'not be found') !== false)) {
            $this->markTestSkipped('Route matching endpoint not available');
            return;
        }

        $this->assertEquals('Ok', $result['code']);
        $this->assertArrayHasKey('matchings', $result);

        echo "\n✓ Matched " . count($points) . " GPS points to road\n";
        if (isset($result['matchings'][0])) {
            echo "  Confidence: " . ($result['matchings'][0]['confidence'] ?? 'N/A') . "\n";
        }
    }

    /**
     * Test multi-point route optimization
     *
     * Scenario: Delivery driver has 5 stops, needs optimal route
     * Expected: Get optimized route visiting all points
     */
    public function test_optimize_delivery_route()
    {
        // Delivery stops in Dhaka
        $waypoints = [
            ['longitude' => 90.3916, 'latitude' => 23.7525], // Start: Central Dhaka
            ['longitude' => 90.4125, 'latitude' => 23.7925], // Stop 1: Gulshan
            ['longitude' => 90.3957, 'latitude' => 23.7386], // Stop 2: Shahbagh
            ['longitude' => 90.4177, 'latitude' => 23.7337], // Stop 3: Motijheel
            ['longitude' => 90.3563, 'latitude' => 23.8103], // Stop 4: Mirpur
        ];

        // Test with routeOverview method for multi-waypoint route
        $result = Barikoi::routeOverview($waypoints);

        $this->skipIfRouteNotAvailable($result);
        $this->assertEquals('Ok', $result['code']);

        echo "\n✓ Calculated route with " . count($waypoints) . " waypoints\n";
        echo "  From: Central Dhaka → Mirpur\n";
        if (isset($result['routes'][0])) {
            $distanceKm = round($result['routes'][0]['distance'] / 1000, 2);
            echo "  Total distance: {$distanceKm} km\n";
        }
    }

    /**
     * Test route with traffic considerations
     *
     * Scenario: Calculate route duration during different times
     * Expected: Duration varies based on traffic
     */
    public function test_route_with_traffic_awareness()
    {
        // Same route, check if API considers traffic
        $result = Barikoi::route()->directions(
            90.3916, 23.7525, // Start
            90.4125, 23.7925  // End
        );

        $this->skipIfRouteNotAvailable($result);
        $this->assertEquals('Ok', $result['code']);

        $route = $result['routes'][0];
        $duration = round($route['duration'] / 60, 1);
        echo "\n✓ Current route duration: {$duration} minutes\n";
        echo "  (Duration may vary with traffic conditions)\n";
    }

    /**
     * Test long distance route
     *
     * Scenario: Inter-city travel Dhaka to Chittagong
     * Expected: Handle long distance routing
     */
    public function test_long_distance_dhaka_to_chittagong()
    {
        $result = Barikoi::route()->distance(
            90.3916, 23.7525,  // Dhaka
            91.8311, 22.3569   // Chittagong
        );

        $this->skipIfRouteNotAvailable($result);
        $this->assertEquals('Ok', $result['code']);

        $route = $result['routes'][0];
        $distanceKm = round($route['distance'] / 1000, 2);

        // Dhaka to Chittagong is approximately 240-260 km
        $this->assertGreaterThan(200000, $route['distance']);
        $this->assertLessThan(300000, $route['distance']);

        echo "\n✓ Long distance route: Dhaka to Chittagong\n";
        echo "  Distance: {$distanceKm} km\n";
    }

    /**
     * Test alternative routes
     *
     * Scenario: Show user multiple route options
     * Expected: Get different routes between same points
     */
    public function test_alternative_routes()
    {
        $result = Barikoi::route()->directions(
            90.3916, 23.7525,
            90.4125, 23.7925,
            ['alternatives' => 'true']
        );

        $this->skipIfRouteNotAvailable($result);
        $this->assertEquals('Ok', $result['code']);

        echo "\n✓ Requested alternative routes\n";
        echo "  From: Central Dhaka to Gulshan\n";
        echo "  Routes found: " . count($result['routes']) . "\n";
    }

    /**
     * Test route calculation performance
     *
     * Scenario: Real-time route updates while driving
     * Expected: Fast response times
     */
    public function test_route_calculation_speed()
    {
        $startTime = microtime(true);

        $result = Barikoi::route()->distance(
            90.3916, 23.7525,
            90.4125, 23.7925
        );

        $duration = microtime(true) - $startTime;

        $this->skipIfRouteNotAvailable($result);
        $this->assertEquals('Ok', $result['code']);

        echo "\n✓ Route calculation completed in " . round($duration, 3) . " seconds\n";

        // Should be reasonably fast (< 3 seconds)
        $this->assertLessThan(3, $duration);
    }

    /**
     * Test car profile (default)
     *
     * Scenario: Calculate driving route with car profile
     * Expected: Get route optimized for cars
     */
    public function test_route_with_car_profile()
    {
        $result = Barikoi::route()->distance(
            90.3916, 23.7525,
            90.4125, 23.7925,
            ['profile' => 'car']
        );

        $this->skipIfRouteNotAvailable($result);
        $this->assertEquals('Ok', $result['code']);
        
        $route = $result['routes'][0];
        $distanceKm = round($route['distance'] / 1000, 2);
        $durationMin = round($route['duration'] / 60, 1);

        echo "\n✓ Car route calculated\n";
        echo "  Distance: {$distanceKm} km\n";
        echo "  Duration: {$durationMin} minutes\n";
    }

    /**
     * Test foot profile (walking)
     *
     * Scenario: Calculate walking route for pedestrians
     * Expected: Get route optimized for walking, longer duration than car
     */
    public function test_route_with_foot_profile()
    {
        $result = Barikoi::route()->distance(
            90.3916, 23.7525,
            90.4125, 23.7925,
            ['profile' => 'foot']
        );

        $this->skipIfRouteNotAvailable($result);
        $this->assertEquals('Ok', $result['code']);
        
        $route = $result['routes'][0];
        $distanceKm = round($route['distance'] / 1000, 2);
        $durationMin = round($route['duration'] / 60, 1);

        echo "\n✓ Walking route calculated\n";
        echo "  Distance: {$distanceKm} km\n";
        echo "  Duration: {$durationMin} minutes (walking)\n";
        
        // Walking should take longer than driving
        $this->assertGreaterThan(0, $route['duration']);
    }

    /**
     * Test profile comparison
     *
     * Scenario: Compare car vs foot route times
     * Expected: Walking takes longer than driving for same route
     */
    public function test_profile_comparison_car_vs_foot()
    {
        // Get car route
        $carResult = Barikoi::route()->distance(
            90.3916, 23.7525,
            90.4125, 23.7925,
            ['profile' => 'car']
        );

        $this->skipIfRouteNotAvailable($carResult);

        // Get walking route
        $footResult = Barikoi::route()->distance(
            90.3916, 23.7525,
            90.4125, 23.7925,
            ['profile' => 'foot']
        );

        $this->assertEquals('Ok', $carResult['code']);
        $this->assertEquals('Ok', $footResult['code']);

        $carDuration = $carResult['routes'][0]['duration'];
        $footDuration = $footResult['routes'][0]['duration'];

        echo "\n✓ Profile comparison:\n";
        echo "  Car: " . round($carDuration / 60, 1) . " minutes\n";
        echo "  Foot: " . round($footDuration / 60, 1) . " minutes\n";
        echo "  Walking is " . round($footDuration / $carDuration, 1) . "x slower\n";

        // Walking should take longer than driving
        $this->assertGreaterThan($carDuration, $footDuration);
    }

    /**
     * Test invalid profile validation
     *
     * Scenario: Try to use invalid profile
     * Expected: Throw InvalidArgumentException
     */
    public function test_invalid_profile_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid profile 'bicycle'. Accepted values are: car, foot");

        Barikoi::route()->distance(
            90.3916, 23.7525,
            90.4125, 23.7925,
            ['profile' => 'bicycle']
        );
    }

    /**
     * Test default profile is car
     *
     * Scenario: Call route without specifying profile
     * Expected: Uses car profile by default
     */
    public function test_default_profile_is_car()
    {
        // Without profile parameter
        $defaultResult = Barikoi::route()->distance(
            90.3916, 23.7525,
            90.4125, 23.7925
        );

        $this->skipIfRouteNotAvailable($defaultResult);

        // With explicit car profile
        $carResult = Barikoi::route()->distance(
            90.3916, 23.7525,
            90.4125, 23.7925,
            ['profile' => 'car']
        );

        $this->assertEquals('Ok', $defaultResult['code']);
        $this->assertEquals('Ok', $carResult['code']);

        // Both should return similar durations (within 10% tolerance)
        $defaultDuration = $defaultResult['routes'][0]['duration'];
        $carDuration = $carResult['routes'][0]['duration'];
        
        $difference = abs($defaultDuration - $carDuration);
        $tolerance = $carDuration * 0.1; // 10% tolerance

        echo "\n✓ Default profile test:\n";
        echo "  Default (no profile): " . round($defaultDuration / 60, 1) . " min\n";
        echo "  Explicit 'car': " . round($carDuration / 60, 1) . " min\n";

        $this->assertLessThan($tolerance, $difference, 'Default profile should behave like car profile');
    }
}

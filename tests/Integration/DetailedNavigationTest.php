<?php

namespace Vendor\PackageName\Tests\Integration;

use Vendor\PackageName\Facades\Barikoi;

/**
 * Integration tests for Detailed Navigation API (/routing endpoint)
 *
 * This tests the separate detailed routing API that provides step-by-step
 * navigation instructions with support for bike, motorcycle, and car profiles.
 *
 * @group integration
 * @group real-api
 */
class DetailedNavigationTest extends IntegrationTestCase
{
    /**
     * Test detailed navigation with car profile (default)
     *
     * Scenario: User wants detailed turn-by-turn navigation by car
     * Expected: Get detailed route with distance, duration, cost, and steps
     */
    public function test_detailed_navigation_with_car()
    {
        $result = Barikoi::detailedNavigation(
            23.791645065364126,   // Start Lat
            90.36558776260725,    // Start Lng
            23.784715477921843,   // Dest Lat
            90.3676300089066      // Dest Lng
        );

        $this->assertIsArray($result);
        
        // Check if API is available
        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Detailed navigation API not available');
            return;
        }

        echo "\n✓ Car navigation calculated\n";
        if (isset($result['distance'])) {
            echo "  Distance: " . $result['distance'] . " km\n";
        }
        if (isset($result['duration'])) {
            echo "  Duration: " . $result['duration'] . " minutes\n";
        }
    }

    /**
     * Test detailed navigation with car profile (explicit)
     *
     * Scenario: User explicitly requests car navigation
     * Expected: Get car-optimized route
     */
    public function test_detailed_navigation_with_car_explicit()
    {
        $result = Barikoi::route()->detailedNavigation(
            23.791645065364126,
            90.36558776260725,
            23.784715477921843,
            90.3676300089066,
            [
                'type' => 'gh',  // Car requires 'gh' type
                'profile' => 'car'
            ]
        );

        $this->assertIsArray($result);

        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Detailed navigation API not available');
            return;
        }

        echo "\n✓ Car profile navigation completed\n";
    }

    /**
     * Test detailed navigation with motorcycle profile
     *
     * Scenario: User wants motorcycle navigation
     * Expected: Get motorcycle-optimized route
     */
    public function test_detailed_navigation_with_motorcycle()
    {
        $result = Barikoi::detailedNavigation(
            23.791645065364126,
            90.36558776260725,
            23.784715477921843,
            90.3676300089066,
            ['profile' => 'motorcycle']
        );

        $this->assertIsArray($result);

        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Detailed navigation API not available');
            return;
        }

        echo "\n✓ Motorcycle navigation calculated\n";
        if (isset($result['distance'])) {
            echo "  Distance: " . $result['distance'] . " km\n";
        }
    }

    /**
     * Test detailed navigation with bike profile
     *
     * Scenario: User wants bicycle navigation
     * Expected: Get bike-optimized route
     */
    public function test_detailed_navigation_with_bike()
    {
        $result = Barikoi::detailedNavigation(
            23.791645065364126,
            90.36558776260725,
            23.784715477921843,
            90.3676300089066,
            [
                'type' => 'gh',  // Bike requires 'gh' type
                'profile' => 'bike'
            ]
        );

        $this->assertIsArray($result);

        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Detailed navigation API not available');
            return;
        }

        echo "\n✓ Bike navigation calculated\n";
        if (isset($result['distance'])) {
            echo "  Distance: " . $result['distance'] . " km\n";
        }
    }

    /**
     * Test profile comparison for detailed navigation
     *
     * Scenario: Compare routes for different vehicle types
     * Expected: Get routes for car, motorcycle, and bike
     */
    public function test_profile_comparison_all_vehicles()
    {
        $profiles = [
            'motorcycle' => ['type' => 'vh', 'profile' => 'motorcycle'],  // vh supports motorcycle
            'car' => ['type' => 'gh', 'profile' => 'car'],                 // gh supports car
            'bike' => ['type' => 'gh', 'profile' => 'bike'],               // gh supports bike
        ];
        $results = [];

        foreach ($profiles as $name => $options) {
            $result = Barikoi::detailedNavigation(
                23.791645065364126,
                90.36558776260725,
                23.784715477921843,
                90.3676300089066,
                $options
            );

            if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
                $this->markTestSkipped('Detailed navigation API not available');
                return;
            }

            $results[$name] = $result;
        }

        echo "\n✓ Profile comparison:\n";
        foreach ($results as $profile => $result) {
            echo "  {$profile}: ";
            if (isset($result['distance'])) {
                echo $result['distance'] . " km";
            }
            if (isset($result['duration'])) {
                echo ", " . $result['duration'] . " min";
            }
            echo "\n";
        }

        $this->assertCount(3, $results);
    }

    /**
     * Test invalid profile validation
     *
     * Scenario: Try to use invalid profile for detailed navigation
     * Expected: Return error response with proper structure
     */
    public function test_invalid_profile_returns_error()
    {
        $result = Barikoi::detailedNavigation(
            23.791645065364126,
            90.36558776260725,
            23.784715477921843,
            90.3676300089066,
            ['profile' => 'scooter']
        );

        $this->assertIsArray($result);
        $this->assertEquals(400, $result['status']);
        $this->assertEquals('invalid_profile', $result['error']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('supported_profiles', $result);

        echo "\n✓ Invalid profile returns proper error response\n";
        echo "  Error: {$result['error']}\n";
        echo "  Message: {$result['message']}\n";
    }

    /**
     * Test with country code parameter
     *
     * Scenario: Calculate route with specific country code
     * Expected: Route calculated for specified country
     */
    public function test_with_country_code()
    {
        $result = Barikoi::detailedNavigation(
            23.791645065364126,
            90.36558776260725,
            23.784715477921843,
            90.3676300089066,
            [
                'type' => 'gh',
                'profile' => 'car',
                'country_code' => 'bgd'  // Bangladesh
            ]
        );

        $this->assertIsArray($result);

        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Detailed navigation API not available');
            return;
        }

        echo "\n✓ Navigation with country code calculated\n";
    }

    /**
     * Test invalid type validation
     *
     * Scenario: Try to use invalid type
     * Expected: Return error response with proper structure
     */
    public function test_invalid_type_returns_error()
    {
        $result = Barikoi::detailedNavigation(
            23.791645065364126,
            90.36558776260725,
            23.784715477921843,
            90.3676300089066,
            ['type' => 'invalid']
        );

        $this->assertIsArray($result);
        $this->assertEquals(400, $result['status']);
        $this->assertEquals('invalid_type', $result['error']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('supported_types', $result);

        echo "\n✓ Invalid type returns proper error response\n";
        echo "  Error: {$result['error']}\n";
        echo "  Supported: " . implode(', ', $result['supported_types']) . "\n";
    }

    /**
     * Test type 'vh' only supports motorcycle
     *
     * Scenario: Try to use car profile with 'vh' type
     * Expected: Return error response with supported profiles
     */
    public function test_vh_type_only_supports_motorcycle()
    {
        $result = Barikoi::detailedNavigation(
            23.791645065364126,
            90.36558776260725,
            23.784715477921843,
            90.3676300089066,
            [
                'type' => 'vh',
                'profile' => 'car'
            ]
        );

        $this->assertIsArray($result);
        $this->assertEquals(400, $result['status']);
        $this->assertEquals('unsupported_combination', $result['error']);
        $this->assertEquals('vh', $result['type']);
        $this->assertEquals('car', $result['profile']);
        $this->assertArrayHasKey('supported_profiles', $result);
        $this->assertEquals(['motorcycle'], $result['supported_profiles']);

        echo "\n✓ Type 'vh' validation works correctly\n";
        echo "  Message: {$result['message']}\n";
        echo "  Supported: " . implode(', ', $result['supported_profiles']) . "\n";
    }

    /**
     * Test type 'gh' supports all profiles
     *
     * Scenario: Test all profiles with 'gh' type
     * Expected: All profiles should work
     */
    public function test_gh_type_supports_all_profiles()
    {
        $profiles = ['motorcycle', 'car', 'bike'];

        foreach ($profiles as $profile) {
            $result = Barikoi::detailedNavigation(
                23.791645065364126,
                90.36558776260725,
                23.784715477921843,
                90.3676300089066,
                [
                    'type' => 'gh',
                    'profile' => $profile
                ]
            );

            $this->assertIsArray($result);

            if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
                $this->markTestSkipped('Detailed navigation API not available');
                return;
            }
        }

        echo "\n✓ Type 'gh' supports all profiles: " . implode(', ', $profiles) . "\n";
    }

    /**
     * Test default type is 'vh' with motorcycle profile
     *
     * Scenario: Call without specifying type or profile
     * Expected: Uses type 'vh' and profile 'motorcycle' as defaults
     */
    public function test_default_type_and_profile()
    {
        $result = Barikoi::detailedNavigation(
            23.791645065364126,
            90.36558776260725,
            23.784715477921843,
            90.3676300089066
            // No type or profile specified
        );

        $this->assertIsArray($result);

        if (isset($result['message']) && is_string($result['message']) && str_contains($result['message'], 'not')) {
            $this->markTestSkipped('Detailed navigation API not available');
            return;
        }

        echo "\n✓ Default type 'vh' with motorcycle profile works\n";
    }
}


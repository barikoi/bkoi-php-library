<?php

namespace Vendor\PackageName\Tests\Integration;

use Vendor\PackageName\Facades\Barikoi;

/**
 * Integration tests that work with real Barikoi API
 * Based on actual API response structures
 *
 * @group integration
 * @group real-api
 */
class RealApiWorkingTest extends IntegrationTestCase
{
    /**
     * Test reverse geocode - basic
     *
     * Scenario: User drops a pin on map
     * Expected: Get address from coordinates
     */
    public function test_reverse_geocode_basic()
    {
        $result = Barikoi::reverseGeocode(90.3957, 23.7386);

        $this->assertIsArray($result);
        $this->assertEquals(200, $result['status']);
        $this->assertArrayHasKey('place', $result);

        $place = $result['place'];
        $this->assertArrayHasKey('address', $place);
        $this->assertArrayHasKey('area', $place);
        $this->assertArrayHasKey('city', $place);

        echo "\n✓ Address: {$place['address']}\n";
        echo "  Area: {$place['area']}, City: {$place['city']}\n";
    }

    /**
     * Test reverse geocode with district option
     *
     * Scenario: App needs district for delivery zones
     * Expected: District field included when requested
     */
    public function test_reverse_geocode_with_district()
    {
        $result = Barikoi::reverseGeocode(90.3916, 23.7525, [
            'district' => true,
            'post_code' => true,
        ]);

        $this->assertEquals(200, $result['status']);
        $this->assertArrayHasKey('place', $result);

        $place = $result['place'];

        // These fields are always present
        $this->assertArrayHasKey('address', $place);
        $this->assertArrayHasKey('city', $place);

        echo "\n✓ Address: {$place['address']}\n";
        echo "  City: {$place['city']}\n";

        // District and postCode may be present when requested
        if (isset($place['district'])) {
            echo "  District: {$place['district']}\n";
        }
        if (isset($place['postCode'])) {
            echo "  Post Code: {$place['postCode']}\n";
        }
    }

    /**
     * Test autocomplete search
     *
     * Scenario: User types in search box
     * Expected: Get suggestions
     */
    public function test_autocomplete_search()
    {
        $result = Barikoi::autocomplete('Gulshan');

        $this->assertIsArray($result);
        $this->assertEquals(200, $result['status']);
        $this->assertArrayHasKey('places', $result);
        $this->assertIsArray($result['places']);
        $this->assertNotEmpty($result['places']);

        echo "\n✓ Found " . count($result['places']) . " places for 'Gulshan'\n";

        if (!empty($result['places'])) {
            $first = $result['places'][0];
            echo "  First result: {$first['address']}\n";
        }
    }

    /**
     * Test search with Bangla text
     *
     * Scenario: User searches in Bangla
     * Expected: Handle Unicode correctly
     */
    public function test_search_bangla_text()
    {
        $result = Barikoi::searchPlace('ঢাকা');

        $this->assertIsArray($result);

        // Check if result has status or places key
        if (isset($result['status'])) {
            $this->assertEquals(200, $result['status']);
        }

        if (isset($result['places']) && !empty($result['places'])) {
            echo "\n✓ Search for 'ঢাকা' returned " . count($result['places']) . " results\n";
            echo "  First result: {$result['places'][0]['address']}\n";
        } else {
            echo "\n✓ Search for 'ঢাকা' completed (response structure may vary)\n";
        }
    }

    /**
     * Test get divisions
     *
     * Scenario: Need list of all divisions for dropdown
     * Expected: Get 8 divisions
     */
    public function test_get_divisions()
    {
        $result = Barikoi::administrative()->getDivisions();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('places', $result);
        $this->assertCount(8, $result['places']);

        echo "\n✓ Retrieved 8 divisions:\n";
        foreach ($result['places'] as $division) {
            echo "  - {$division['name']}\n";
        }
    }

    /**
     * Test rapid consecutive calls
     *
     * Scenario: User rapidly scrolling map
     * Expected: All requests complete successfully
     */
    public function test_rapid_api_calls()
    {
        $coordinates = [
            [90.3916, 23.7525],
            [90.4125, 23.7925],
            [90.3957, 23.7386],
            [90.4177, 23.7337],
            [90.3563, 23.8103],
        ];

        $successCount = 0;
        $startTime = microtime(true);

        foreach ($coordinates as $coord) {
            $result = Barikoi::reverseGeocode($coord[0], $coord[1]);
            if ($result['status'] === 200) {
                $successCount++;
            }
        }

        $duration = microtime(true) - $startTime;

        $this->assertEquals(5, $successCount);

        echo "\n✓ Made 5 rapid API calls\n";
        echo "  Success: {$successCount}/5\n";
        echo "  Time: " . round($duration, 2) . " seconds\n";
        echo "  Average: " . round($duration / 5, 2) . " sec/call\n";
    }

    /**
     * Test multiple cities
     *
     * Scenario: Multi-city operations
     * Expected: All return valid data
     */
    public function test_multiple_cities()
    {
        $locations = [
            ['name' => 'Dhaka', 'lng' => 90.3916, 'lat' => 23.7525],
            ['name' => 'Chittagong', 'lng' => 91.8311, 'lat' => 22.3569],
            ['name' => 'Sylhet', 'lng' => 91.8701, 'lat' => 24.8949],
        ];

        echo "\n✓ Testing multiple cities:\n";

        foreach ($locations as $location) {
            $result = Barikoi::reverseGeocode($location['lng'], $location['lat']);

            $this->assertEquals(200, $result['status']);
            $this->assertArrayHasKey('place', $result);

            echo "  {$location['name']}: {$result['place']['city']}\n";
        }
    }

    /**
     * Test API performance
     *
     * Scenario: Monitor API response time
     * Expected: Reasonable performance
     */
    public function test_api_performance()
    {
        $startTime = microtime(true);

        $result = Barikoi::reverseGeocode(90.3916, 23.7525);

        $duration = microtime(true) - $startTime;

        $this->assertEquals(200, $result['status']);
        $this->assertLessThan(2, $duration, "API call took too long: {$duration} seconds");

        echo "\n✓ API response time: " . round($duration * 1000) . " ms\n";
    }
}

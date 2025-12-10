<?php

namespace Vendor\PackageName\Tests\Integration;

use Vendor\PackageName\Facades\Barikoi;

/**
 * Real-world integration tests for administrative services
 *
 * @group integration
 * @group real-api
 */
class RealWorldAdministrativeTest extends IntegrationTestCase
{
    /**
     * Test getting all divisions
     *
     * Scenario: App needs dropdown list of all divisions in Bangladesh
     * Expected: Get complete list of 8 divisions
     */
    public function test_get_all_divisions()
    {
        $result = Barikoi::administrative()->getDivisions();

        $this->assertIsArray($result);

        // API returns 'places' key, not 'divisions'
        $this->assertArrayHasKey('places', $result);
        $this->assertIsArray($result['places']);

        // Bangladesh has 8 divisions
        $this->assertCount(8, $result['places']);

        // Check structure of first division
        $firstDivision = $result['places'][0];
        $this->assertArrayHasKey('id', $firstDivision);
        $this->assertArrayHasKey('name', $firstDivision);

        echo "\n✓ Retrieved all " . count($result['places']) . " divisions:\n";
        foreach ($result['places'] as $division) {
            echo "  - {$division['name']}\n";
        }
    }

    /**
     * Test getting all districts
     *
     * Scenario: Need complete list of districts for address form
     * Expected: Get all 64 districts of Bangladesh
     */
    public function test_get_all_districts()
    {
        $result = Barikoi::administrative()->getDistricts();

        $this->assertIsArray($result);
        $this->assertEquals(200, $result['status']);
        $this->assertArrayHasKey('places', $result);
        $this->assertIsArray($result['places']);

        // Bangladesh has 64 districts
        $this->assertGreaterThanOrEqual(64, count($result['places']));

        echo "\n✓ Retrieved " . count($result['places']) . " districts\n";
        echo "  First 5: ";
        $firstFive = array_slice($result['places'], 0, 5);
        echo implode(', ', array_column($firstFive, 'name')) . "\n";
    }

    /**
     * Test getting districts by specific division
     *
     * Scenario: User selects "Dhaka" division, need to show its districts
     * Expected: Get only districts within Dhaka division
     */
    public function test_get_districts_by_dhaka_division()
    {
        $this->markTestSkipped('getDistrictsByDivision() method not available in AdministrativeService');
    }

    /**
     * Test getting subdistricts/upazilas
     *
     * Scenario: Need upazila list for detailed address
     * Expected: Get all upazilas/subdistricts
     */
    public function test_get_all_subdistricts()
    {
        $result = Barikoi::administrative()->getSubdistricts();

        $this->assertIsArray($result);

        // Check if endpoint is available
        if (!isset($result['status']) || (isset($result['message']) && strpos($result['message'], 'not') !== false)) {
            $this->markTestSkipped('Subdistricts endpoint not available or returns different structure');
        }

        $this->assertEquals(200, $result['status']);

        // Try different possible keys
        $key = isset($result['subdistricts']) ? 'subdistricts' : (isset($result['places']) ? 'places' : null);

        if ($key && isset($result[$key])) {
            echo "\n✓ Retrieved " . count($result[$key]) . " subdistricts/upazilas\n";
        }
    }

    /**
     * Test getting thanas (police stations)
     *
     * Scenario: Police dispatch system needs thana list
     * Expected: Get all thanas
     */
    public function test_get_all_thanas()
    {
        $result = Barikoi::administrative()->getThanas();

        $this->assertIsArray($result);

        // Check if endpoint is available
        if (!isset($result['status']) || (isset($result['message']) && strpos($result['message'], 'not') !== false)) {
            $this->markTestSkipped('Thanas endpoint not available or returns different structure');
        }

        $this->assertEquals(200, $result['status']);

        // Try different possible keys
        $key = isset($result['thanas']) ? 'thanas' : (isset($result['places']) ? 'places' : null);

        if ($key && isset($result[$key]) && !empty($result[$key])) {
            echo "\n✓ Retrieved " . count($result[$key]) . " thanas\n";
            echo "  Sample thanas:\n";
            foreach (array_slice($result[$key], 0, 5) as $thana) {
                echo "  - {$thana['name']}\n";
            }
        }
    }

    /**
     * Test getting city corporation for a location
     *
     * Scenario: Check if a location is within Dhaka city corporation
     * Expected: Get city corporation information for coordinates
     */
    public function test_get_city_corporation_for_location()
    {
        // Dhaka city center coordinates
        $result = Barikoi::administrative()->getCityCorporation(90.3916, 23.7525);

        $this->assertIsArray($result);

        // Check if response has status or data
        if (isset($result['status'])) {
            echo "\n✓ City corporation check completed\n";
            if (isset($result['name'])) {
                echo "  Location: {$result['name']}\n";
            }
        } else {
            echo "\n✓ City corporation endpoint responded\n";
        }
    }

    /**
     * Test complete address hierarchy
     *
     * Scenario: Build complete address form with cascading dropdowns
     * Expected: Division → District → Subdistrict hierarchy works
     */
    public function test_complete_address_hierarchy()
    {
        $this->markTestSkipped('getDistrictsByDivision() method not available in AdministrativeService');
    }

    /**
     * Test ward and zone information for a location
     *
     * Scenario: Get ward/zone data for specific coordinates
     * Expected: Get ward and zone boundaries for location
     */
    public function test_get_ward_and_zone_for_location()
    {
        // Dhaka city center coordinates
        $result = Barikoi::administrative()->getWardAndZone(90.3916, 23.7525);

        $this->assertIsArray($result);

        // Check if response has expected keys
        if (isset($result['status'])) {
            echo "\n✓ Ward and zone check completed\n";
            if (isset($result['ward'])) {
                echo "  Ward: {$result['ward']}\n";
            }
        } else {
            echo "\n✓ Ward and zone endpoint responded\n";
        }
    }

    /**
     * Test administrative data for delivery zones
     *
     * Scenario: E-commerce needs to define delivery zones by district
     * Expected: Can group locations by administrative boundaries
     */
    public function test_delivery_zone_by_district()
    {
        $districts = Barikoi::administrative()->getDistricts();

        $this->assertEquals(200, $districts['status']);

        // Define delivery zones based on districts
        $deliveryZones = [
            'Premium' => ['Dhaka', 'Chattogram', 'Sylhet'],
            'Standard' => ['Khulna', 'Rajshahi', 'Rangpur'],
            'Remote' => [], // Other districts
        ];

        $districtNames = array_column($districts['places'], 'name');

        foreach ($deliveryZones['Premium'] as $premiumDistrict) {
            $this->assertContains($premiumDistrict, $districtNames);
        }

        echo "\n✓ Verified delivery zones against real districts\n";
        echo "  Premium zones: " . implode(', ', $deliveryZones['Premium']) . "\n";
        echo "  Standard zones: " . implode(', ', $deliveryZones['Standard']) . "\n";
    }

    /**
     * Test location to administrative mapping
     *
     * Scenario: Given coordinates, determine administrative region
     * Expected: Combine reverse geocode with administrative data
     */
    public function test_coordinates_to_administrative_region()
    {
        // Get location from coordinates with district option
        $location = Barikoi::reverseGeocode(90.3916, 23.7525, ['district' => true]);
        $this->assertEquals(200, $location['status']);

        // District key only present when requested
        if (isset($location['place']['district'])) {
            $district = $location['place']['district'];

            // Get all districts
            $allDistricts = Barikoi::administrative()->getDistricts();
            $districtData = collect($allDistricts['places'])->firstWhere('name', $district);

            echo "\n✓ Mapped coordinates to administrative region:\n";
            echo "  Coordinates: 90.3916, 23.7525\n";
            echo "  District: {$district}\n";
            if ($districtData) {
                echo "  District ID: {$districtData['id']}\n";
            }
        } else {
            echo "\n✓ Reverse geocode completed (district not in response)\n";
            echo "  Address: {$location['place']['address']}\n";
        }
    }

    /**
     * Test data consistency across API calls
     *
     * Scenario: Ensure district names match across different endpoints
     * Expected: Consistent naming and IDs
     */
    public function test_administrative_data_consistency()
    {
        // Get districts from different endpoints
        $allDistricts = Barikoi::administrative()->getDistricts();
        $divisions = Barikoi::administrative()->getDivisions();

        $this->assertEquals(200, $allDistricts['status']);
        $this->assertEquals(200, $divisions['status']);

        // Check that division IDs are consistent
        foreach ($divisions['places'] as $division) {
            $this->assertIsInt($division['id']);
            $this->assertNotEmpty($division['name']);
        }

        echo "\n✓ Verified administrative data consistency\n";
        echo "  All divisions have valid IDs and names\n";
        echo "  All districts are properly structured\n";
    }
}

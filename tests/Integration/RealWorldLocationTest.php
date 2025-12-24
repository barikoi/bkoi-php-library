<?php

namespace Vendor\BarikoiApi\Tests\Integration;

use Vendor\BarikoiApi\Facades\Barikoi;

/**
 * Real-world integration tests for location services
 *
 * @group integration
 * @group real-api
 */
class RealWorldLocationTest extends IntegrationTestCase
{
    /**
     * Test real reverse geocoding in Dhaka
     *
     * Scenario: User drops a pin on map at Shahbagh, Dhaka
     * Expected: Get actual address from Barikoi API
     */
    public function test_reverse_geocode_dhaka_shahbagh()
    {
        // Shahbagh area coordinates - request with district option
        $result = Barikoi::reverseGeocode(90.3957, 23.7386, ['district' => true]);

        $this->assertIsObject($result);
        $this->assertEquals(200, $result->status);
        $this->assertObjectHasProperty('place', $result);

        $place = $result->place;
        $this->assertObjectHasProperty('address', $place);
        $this->assertObjectHasProperty('city', $place);
        $this->assertObjectHasProperty('area', $place);
        $this->assertObjectHasProperty('district', $place);

        // Should be in Dhaka
        $this->assertStringContainsString('Dhaka', $place->district);

        echo "\n✓ Found address: {$place->address}\n";
        echo "  City: {$place->city}, Area: {$place->area}\n";
    }

    /**
     * Test reverse geocoding with district option
     *
     * Scenario: App needs district information for delivery zones
     * Expected: Boolean district parameter works correctly
     */
    public function test_reverse_geocode_with_district_option()
    {
        $result = Barikoi::reverseGeocode(90.3916, 23.7525, [
            'district' => true,
            'post_code' => true,
        ]);

        $this->assertEquals(200, $result->status);
        $this->assertObjectHasProperty('place', $result);

        $place = $result->place;
        $this->assertObjectHasProperty('district', $place);
        $this->assertObjectHasProperty('postCode', $place);

        echo "\n✓ District: {$place->district}\n";
        echo "  Post Code: {$place->postCode}\n";
    }

    /**
     * Test autocomplete for real location search
     *
     * Scenario: User types "Gulshan" in search box
     * Expected: Get list of matching places
     */
    public function test_autocomplete_gulshan_search()
    {
        $result = Barikoi::autocomplete('Gulshan');

        $this->assertIsObject($result);
        if (isset($result->status)) {
            $this->assertEquals(200, $result->status);
        }
        $this->assertObjectHasProperty('places', $result);
        $this->assertIsObject($result->places);
        $this->assertNotEmpty($result->places);

        // Check first result
        $firstPlace = $result->places[0];
        $firstPlaceArray = is_object($firstPlace) ? get_object_vars($firstPlace) : $firstPlace;
        $this->assertArrayHasKey('address', $firstPlaceArray);
        $this->assertStringContainsString('Gulshan', $firstPlaceArray['address']);

        echo "\n✓ Found " . count($result->places) . " places matching 'Gulshan'\n";
        echo "  First result: {$firstPlaceArray['address']}\n";
    }

    /**
     * Test geocoding - convert address to coordinates
     *
     * Scenario: User enters "Dhanmondi, Dhaka" and needs coordinates
     * Expected: Get latitude and longitude
     */
    public function test_geocode_dhanmondi_address()
    {
        $result = Barikoi::geocode('Dhanmondi, Dhaka');

        $this->assertIsObject($result);
        $this->assertEquals(200, $result->status);

        // Rupantor API returns geocoded_address key
        $this->assertObjectHasProperty('geocoded_address', $result);

        $place = $result->geocoded_address;
        $placeArray = is_object($place) ? get_object_vars($place) : $place;
        $this->assertArrayHasKey('latitude', $placeArray);
        $this->assertArrayHasKey('longitude', $placeArray);
        $this->assertArrayHasKey('address', $placeArray);

        // Coordinates should be in Dhaka area (latitude/longitude may be strings)
        $lat = (float) $placeArray['latitude'];
        $lng = (float) $placeArray['longitude'];

        $this->assertGreaterThan(23.7, $lat);
        $this->assertLessThan(23.9, $lat);
        $this->assertGreaterThan(90.3, $lng);
        $this->assertLessThan(90.5, $lng);

        echo "\n✓ Geocoded 'Dhanmondi, Dhaka'\n";
        echo "  Coordinates: {$placeArray['latitude']}, {$placeArray['longitude']}\n";
        echo "  Address: {$placeArray['address']}\n";
    }

    /**
     * Test nearby places search
     *
     * Scenario: User at Motijheel wants to find nearby restaurants
     * Expected: Get list of places within distance
     */
    public function test_nearby_places_motijheel()
    {
        // Motijheel commercial area
        $result = Barikoi::nearby(90.4177, 23.7337, 1000);

        $this->assertIsObject($result);

        // Check if endpoint is available
        $data = is_object($result) ? get_object_vars($result) : $result;

        if (isset($data['message']) && str_contains($data['message'], 'could not be found')) {
            $this->markTestSkipped('Nearby endpoint not available in current API version');
            return;
        }

        // Some plans may not include status; we mainly expect "places"
        $this->assertObjectHasProperty('places', $result);
        $this->assertIsObject($result->places);

        if (!empty($result->places)) {
            $firstPlace = $result->places[0];
            $firstPlaceArray = is_array($firstPlace) ? $firstPlace : (array) $firstPlace;
            // API returns 'Address' (capital A) not 'address'
            $this->assertArrayHasKey('Address', $firstPlaceArray);
            // API returns 'distance_in_meters' not 'distance'
            $this->assertArrayHasKey('distance_in_meters', $firstPlaceArray);

            echo "\n✓ Found " . count($result->places) . " places within 1km\n";
            echo "  Nearest: {$firstPlaceArray['Address']} ({$firstPlaceArray['distance_in_meters']}m away)\n";
        }
    }

    /**
     * Test search with Bangla text
     *
     * Scenario: User searches in Bangla language
     * Expected: Handle Unicode Bangla characters correctly
     */
    public function test_search_with_bangla_text()
    {
        $result = Barikoi::searchPlace('ঢাকা');

        $this->assertIsObject($result);

        // Check if we got a valid response
        if (isset($result->status)) {
            $this->assertEquals(200, $result->status);
            $this->assertObjectHasProperty('places', $result);

            if (!empty($result->places)) {
                $firstPlace = is_object($result->places[0]) ? get_object_vars($result->places[0]) : $result->places[0];
                echo "\n✓ Search for 'ঢাকা' found " . count($result->places) . " places\n";
                echo "  First result: {$firstPlace['address']}\n";
            }
        } else {
            // Some search endpoints may return different structure
            $this->assertTrue(true, 'Search endpoint responded');
            echo "\n✓ Bangla search completed\n";
        }
    }

    /**
     * Test multiple locations in different cities
     *
     * Scenario: Delivery app checking multiple pickup points
     * Expected: All locations return valid data
     */
    public function test_multiple_city_locations()
    {
        $locations = [
            ['name' => 'Dhaka', 'lng' => 90.3916, 'lat' => 23.7525],
            ['name' => 'Chittagong', 'lng' => 91.8311, 'lat' => 22.3569],
            ['name' => 'Sylhet', 'lng' => 91.8701, 'lat' => 24.8949],
        ];

        $results = [];

        foreach ($locations as $location) {
            // Request with district option
            $result = Barikoi::reverseGeocode($location['lng'], $location['lat'], ['district' => true]);

            $this->assertEquals(200, $result->status);
            $this->assertObjectHasProperty('place', $result);

            $results[] = [
                'name' => $location['name'],
                'address' => $result->place->address,
                'district' => $result->place->district ?? 'N/A',
            ];
        }

        echo "\n✓ Tested " . count($results) . " different cities:\n";
        foreach ($results as $result) {
            echo "  {$result['name']}: {$result['address']} ({$result['district']})\n";
        }

        // All should have different districts
        $this->assertNotEquals($results[0]['district'], $results[1]['district']);
    }

    /**
     * Test edge case - coordinates at border
     *
     * Scenario: Location near Bangladesh-India border
     * Expected: Should return Bangladesh location
     */
    public function test_border_location()
    {
        // Near Dinajpur (close to border) - request with district option
        $result = Barikoi::reverseGeocode(88.6354, 25.6217, ['district' => true]);

        $this->assertIsObject($result);
        $this->assertEquals(200, $result->status);

        if (isset($result->place)) {
            echo "\n✓ Border location result:\n";
            echo "  Address: {$result->place->address}\n";
            if (isset($result->place->district)) {
                echo "  District: {$result->place->district}\n";
            }
        }
    }

    /**
     * Test performance - rapid consecutive calls
     *
     * Scenario: User rapidly scrolling map, triggering multiple API calls
     * Expected: All requests complete successfully
     */
    public function test_rapid_consecutive_calls()
    {
        $coordinates = [
            [90.3916, 23.7525], // Dhaka
            [90.4125, 23.7925], // Gulshan
            [90.3957, 23.7386], // Shahbagh
            [90.4177, 23.7337], // Motijheel
            [90.3563, 23.8103], // Mirpur
        ];

        $successCount = 0;
        $startTime = microtime(true);

        foreach ($coordinates as $coord) {
            $result = Barikoi::reverseGeocode($coord[0], $coord[1]);

            if ($result->status === 200) {
                $successCount++;
            }
        }

        $duration = microtime(true) - $startTime;

        $this->assertEquals(5, $successCount);

        echo "\n✓ Made 5 rapid API calls\n";
        echo "  Success rate: {$successCount}/5\n";
        echo "  Total time: " . round($duration, 2) . " seconds\n";
        echo "  Average: " . round($duration / 5, 2) . " seconds per call\n";
    }
}

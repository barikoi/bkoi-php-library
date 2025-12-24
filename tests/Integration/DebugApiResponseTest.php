<?php

namespace Vendor\BarikoiApi\Tests\Integration;

use Vendor\BarikoiApi\Facades\Barikoi;

/**
 * Debug test to see actual API responses
 *
 * @group integration
 * @group debug
 */
class DebugApiResponseTest extends IntegrationTestCase
{
    /**
     * Debug: See what reverse geocode actually returns
     */
    public function test_debug_reverse_geocode()
    {
        $result = Barikoi::reverseGeocode(90.3957, 23.7386);

        echo "\n\n=== REVERSE GEOCODE RESPONSE ===\n";
        echo json_encode($result, JSON_PRETTY_PRINT);
        echo "\n================================\n\n";

        $this->assertIsObject($result);
    }

    /**
     * Debug: See what nearby actually returns
     */
    public function test_debug_nearby()
    {
        $result = Barikoi::nearby(90.4177, 23.7337, 1000);

        echo "\n\n=== NEARBY RESPONSE ===\n";
        echo json_encode($result, JSON_PRETTY_PRINT);
        echo "\n=======================\n\n";

        $this->assertIsObject($result);
    }

    /**
     * Debug: See what geocode actually returns
     */
    public function test_debug_geocode()
    {
        $result = Barikoi::geocode('Dhanmondi, Dhaka');

        echo "\n\n=== GEOCODE RESPONSE ===\n";
        echo json_encode($result, JSON_PRETTY_PRINT);
        echo "\n========================\n\n";

        $this->assertIsObject($result);
    }

    /**
     * Debug: See what route actually returns
     */
    public function test_debug_route()
    {
        $result = Barikoi::routeOverview([
            ['longitude' => 90.3563, 'latitude' => 23.8103],
            ['longitude' => 90.4125, 'latitude' => 23.7925],
        ]);

        echo "\n\n=== ROUTE RESPONSE ===\n";
        echo json_encode($result, JSON_PRETTY_PRINT);
        echo "\n======================\n\n";

        $this->assertIsObject($result);
    }
}

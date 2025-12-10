<?php

namespace Vendor\PackageName\Tests\Feature;

use Vendor\PackageName\Tests\TestCase;
use Vendor\PackageName\Facades\Barikoi;

class BarikoiAdministrativeTest extends TestCase
{
    /**
     * Test get divisions
     *
     * @return void
     */
    public function test_get_divisions()
    {
        $result = Barikoi::administrative()->getDivisions();

        $this->assertIsArray($result);
    }

    /**
     * Test get districts
     *
     * @return void
     */
    public function test_get_districts()
    {
        $result = Barikoi::administrative()->getDistricts();

        $this->assertIsArray($result);
    }

    /**
     * Test get districts by division
     *
     * @return void
     */
    public function test_get_districts_by_division()
    {
        $result = Barikoi::administrative()->getDistricts('Dhaka');

        $this->assertIsArray($result);
    }

    /**
     * Test get subdistricts
     *
     * @return void
     */
    public function test_get_subdistricts()
    {
        $result = Barikoi::administrative()->getSubdistricts('Dhaka');

        $this->assertIsArray($result);
    }

    /**
     * Test get thanas
     *
     * @return void
     */
    public function test_get_thanas()
    {
        $result = Barikoi::administrative()->getThanas('Dhaka');

        $this->assertIsArray($result);
    }

    /**
     * Test get ward and zone
     *
     * @return void
     */
    public function test_get_ward_and_zone()
    {
        $result = Barikoi::administrative()->getWardAndZone(90.3572, 23.8067);

        $this->assertIsArray($result);
    }

    /**
     * Test get city corporation
     *
     * @return void
     */
    public function test_get_city_corporation()
    {
        $result = Barikoi::administrative()->getCityCorporation(90.3572, 23.8067);

        $this->assertIsArray($result);
    }
}

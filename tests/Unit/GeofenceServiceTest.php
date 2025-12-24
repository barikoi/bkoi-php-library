<?php

namespace Vendor\BarikoiApi\Tests\Unit;

use Vendor\BarikoiApi\Tests\TestCase;
use Vendor\BarikoiApi\BarikoiClient;
use Vendor\BarikoiApi\Services\GeofenceService;
use Vendor\BarikoiApi\Exceptions\BarikoiValidationException;
use Illuminate\Support\Facades\Http;

class GeofenceServiceTest extends TestCase
{
    public function test_geofence_check_nearby_valid()
    {
        $client = new \Vendor\BarikoiApi\BarikoiClient('test-key', 'https://barikoi.xyz/');
        $service = new \Vendor\BarikoiApi\Services\GeofenceService($client);
//        \Illuminate\Support\Facades\Http::fake([
//            '*' => \Illuminate\Support\Facades\Http::response([
//                'id' => true,
//                'distance' => 13.2,
//                'status' => 200
//            ], 200)
//        ]);
        $result = $service->checkNearby(23.76245538673939, 90.37852866512583,  23.762412943322726, 90.37864864706823,50);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(200, $result['status']);
    }

    public function test_geofence_check_nearby_invalid_latitude()
    {
        $client = new \Vendor\BarikoiApi\BarikoiClient('test-key', 'https://barikoi.xyz/');
        $service = new \Vendor\BarikoiApi\Services\GeofenceService($client);
        $this->expectException(\Vendor\BarikoiApi\Exceptions\BarikoiValidationException::class);
        $service->checkNearby(100.0, 90.37852866512583, 50, 23.762412943322726, 90.37864864706823);
    }

    public function test_geofence_check_nearby_invalid_longitude()
    {
        $client = new \Vendor\BarikoiApi\BarikoiClient('test-key', 'https://barikoi.xyz/');
        $service = new \Vendor\BarikoiApi\Services\GeofenceService($client);
        $this->expectException(\Vendor\BarikoiApi\Exceptions\BarikoiValidationException::class);
        $service->checkNearby(23.76245538673939, 200.0, 50, 23.762412943322726, 90.37864864706823);
    }


    public function test_geofence_check_nearby_invalid_radius()
    {
        $client = new \Vendor\BarikoiApi\BarikoiClient('test-key', 'https://barikoi.xyz/');
        $service = new \Vendor\BarikoiApi\Services\GeofenceService($client);
        $this->expectException(\Vendor\BarikoiApi\Exceptions\BarikoiValidationException::class);
        $service->checkNearby(
            23.76245538673939, // destinationLatitude
            90.37852866512583, // destinationLongitude
            23.762412943322726, // currentLatitude
            90.37864864706823, // currentLongitude
            0 // radius invalid
        );
    }
}


<?php

namespace Barikoi\BarikoiApis\Tests\Unit;

use Barikoi\BarikoiApis\Tests\TestCase;
use Barikoi\BarikoiApis\BarikoiClient;
use Barikoi\BarikoiApis\Services\GeofenceService;
use Barikoi\BarikoiApis\Exceptions\BarikoiValidationException;
use Illuminate\Support\Facades\Http;

class GeofenceServiceTest extends TestCase
{
    public function test_geofence_check_nearby_valid()
    {
        $client = new \Barikoi\BarikoiApis\BarikoiClient('test-key', 'https://barikoi.xyz/');
        $service = new \Barikoi\BarikoiApis\Services\GeofenceService($client);
//        \Illuminate\Support\Facades\Http::fake([
//            '*' => \Illuminate\Support\Facades\Http::response([
//                'id' => true,
//                'distance' => 13.2,
//                'status' => 200
//            ], 200)
//        ]);

        $result = $service->checkNearby(23.76245538673939, 90.37852866512583,  23.762412943322726, 90.37864864706823,50);
        $this->assertIsObject($result);
        $this->assertObjectHasProperty('data', $result);
        $this->assertObjectHasProperty('message', $result);
        $this->assertEquals(200, $result->status);

    }

    public function test_geofence_check_nearby_invalid_latitude()
    {
        $client = new \Barikoi\BarikoiApis\BarikoiClient('test-key', 'https://barikoi.xyz/');
        $service = new \Barikoi\BarikoiApis\Services\GeofenceService($client);
        $this->expectException(\Barikoi\BarikoiApis\Exceptions\BarikoiValidationException::class);
        $service->checkNearby(100.0, 90.37852866512583, 50, 23.762412943322726, 90.37864864706823);
    }

    public function test_geofence_check_nearby_invalid_longitude()
    {
        $client = new \Barikoi\BarikoiApis\BarikoiClient('test-key', 'https://barikoi.xyz/');
        $service = new \Barikoi\BarikoiApis\Services\GeofenceService($client);
        $this->expectException(\Barikoi\BarikoiApis\Exceptions\BarikoiValidationException::class);
        $service->checkNearby(23.76245538673939, 200.0, 50, 23.762412943322726, 90.37864864706823);
    }


    public function test_geofence_check_nearby_invalid_radius()
    {
        $client = new \Barikoi\BarikoiApis\BarikoiClient('test-key', 'https://barikoi.xyz/');
        $service = new \Barikoi\BarikoiApis\Services\GeofenceService($client);
        $this->expectException(\Barikoi\BarikoiApis\Exceptions\BarikoiValidationException::class);
        $service->checkNearby(
            23.76245538673939, // destinationLatitude
            90.37852866512583, // destinationLongitude
            23.762412943322726, // currentLatitude
            90.37864864706823, // currentLongitude
            0 // radius invalid
        );
    }
}


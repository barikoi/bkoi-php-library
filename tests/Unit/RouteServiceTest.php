<?php

namespace Vendor\PackageName\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vendor\PackageName\BarikoiClient;
use Vendor\PackageName\Services\RouteService;

/**
 * Unit tests for RouteService profile validation
 */
class RouteServiceTest extends TestCase
{
    protected RouteService $routeService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the client
        $client = $this->createMock(BarikoiClient::class);
        $this->routeService = new RouteService($client);
    }

    /**
     * Test that valid 'car' profile is accepted
     */
    public function test_car_profile_is_valid()
    {
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->routeService);
        $method = $reflection->getMethod('validateProfile');
        $method->setAccessible(true);

        $result = $method->invoke($this->routeService, ['profile' => 'car']);

        $this->assertEquals('car', $result['profile']);
    }

    /**
     * Test that valid 'foot' profile is accepted
     */
    public function test_foot_profile_is_valid()
    {
        $reflection = new \ReflectionClass($this->routeService);
        $method = $reflection->getMethod('validateProfile');
        $method->setAccessible(true);

        $result = $method->invoke($this->routeService, ['profile' => 'foot']);

        $this->assertEquals('foot', $result['profile']);
    }

    /**
     * Test that default profile is 'car'
     */
    public function test_default_profile_is_car()
    {
        $reflection = new \ReflectionClass($this->routeService);
        $method = $reflection->getMethod('validateProfile');
        $method->setAccessible(true);

        $result = $method->invoke($this->routeService, []);

        $this->assertEquals('car', $result['profile']);
    }

    /**
     * Test that invalid profile throws exception
     */
    public function test_invalid_profile_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid profile 'bike'. Accepted values are: car, foot");

        $reflection = new \ReflectionClass($this->routeService);
        $method = $reflection->getMethod('validateProfile');
        $method->setAccessible(true);

        $method->invoke($this->routeService, ['profile' => 'bike']);
    }

    /**
     * Test that profile constants are defined correctly
     */
    public function test_profile_constants()
    {
        $this->assertEquals('car', RouteService::PROFILE_CAR);
        $this->assertEquals('foot', RouteService::PROFILE_FOOT);
    }

    /**
     * Test empty string profile throws exception
     */
    public function test_empty_profile_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);

        $reflection = new \ReflectionClass($this->routeService);
        $method = $reflection->getMethod('validateProfile');
        $method->setAccessible(true);

        $method->invoke($this->routeService, ['profile' => '']);
    }

    /**
     * Test case sensitivity - profile should be case-sensitive
     */
    public function test_profile_is_case_sensitive()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid profile 'Car'. Accepted values are: car, foot");

        $reflection = new \ReflectionClass($this->routeService);
        $method = $reflection->getMethod('validateProfile');
        $method->setAccessible(true);

        $method->invoke($this->routeService, ['profile' => 'Car']);
    }

    /**
     * Test that other options are preserved
     */
    public function test_other_options_are_preserved()
    {
        $reflection = new \ReflectionClass($this->routeService);
        $method = $reflection->getMethod('validateProfile');
        $method->setAccessible(true);

        $result = $method->invoke($this->routeService, [
            'profile' => 'foot',
            'geometries' => 'polyline',
            'steps' => true,
        ]);

        $this->assertEquals('foot', $result['profile']);
        $this->assertEquals('polyline', $result['geometries']);
        $this->assertTrue($result['steps']);
    }
}


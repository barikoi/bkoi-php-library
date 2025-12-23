<?php

namespace Vendor\BarikoiApi\Tests\Integration;

use Vendor\BarikoiApi\Tests\TestCase;

/**
 * Base class for integration tests that make real API calls
 *
 * IMPORTANT: These tests require a real Barikoi API key!
 * Set BARIKOI_API_KEY environment variable before running.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Load .env file if it exists
        $envPath = __DIR__ . '/../../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value);
                    if (!empty($name) && !isset($_ENV[$name])) {
                        $_ENV[$name] = $value;
                        putenv("$name=$value");
                    }
                }
            }
        }

        // Check if API key is available (from environment or .env file)
        $apiKey = getenv('BARIKOI_API_KEY') ?: env('BARIKOI_API_KEY');

        if (empty($apiKey)) {
            $this->markTestSkipped(
                'Integration tests require BARIKOI_API_KEY environment variable. ' .
                'Add to .env file or run: BARIKOI_API_KEY=your_key vendor/bin/phpunit --group integration'
            );
        }

        // Configure the package to use real API
        config(['barikoi.api_key' => $apiKey]);
        config(['barikoi.base_url' => getenv('BARIKOI_BASE_URL') ?: env('BARIKOI_BASE_URL', 'https://barikoi.xyz/v2/api')]);

        // DO NOT mock HTTP - let real requests go through
        // Http::fake() is NOT called here
    }

    /**
     * Real coordinates in Dhaka for testing
     */
    protected function getDhakaCoordinates(): array
    {
        return [
            'longitude' => 90.3916,
            'latitude' => 23.7525,
        ];
    }

    /**
     * Real coordinates in Chittagong for testing
     */
    protected function getChittagongCoordinates(): array
    {
        return [
            'longitude' => 91.8311,
            'latitude' => 22.3569,
        ];
    }

    /**
     * Real place ID for testing (Mirpur DOHS)
     */
    protected function getRealPlaceId(): string
    {
        return 'XkZDC0wwwFCjQaLmNDR2sE8v09QKYu'; // Example - may need updating
    }
}

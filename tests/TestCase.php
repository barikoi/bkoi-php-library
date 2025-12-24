<?php

namespace Vendor\BarikoiApi\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Vendor\BarikoiApi\BarikoiServiceProvider;
use Illuminate\Support\Facades\Http;

class TestCase extends Orchestra
{
    /**
     * The latest test response (required by Orchestra Testbench).
     *
     * @var \Illuminate\Testing\TestResponse|null
     */
    public static $latestResponse;

    /**
     * Track if .env file has been loaded
     *
     * @var bool
     */
    protected static $envLoaded = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Load .env file for local testing (if not already loaded and file exists)
        if (!static::$envLoaded && file_exists(__DIR__ . '/../.env')) {
            if (class_exists(\Dotenv\Dotenv::class)) {
                $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
                $dotenv->safeLoad(); // safeLoad won't override existing env vars
                static::$envLoaded = true;
            }
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Enable Http recording for Discord notifications with curl commands
        if (class_exists('Illuminate\Support\Facades\Http') && method_exists('Illuminate\Support\Facades\Http', 'record')) {
            Http::record();
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            BarikoiServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup Barikoi configuration
        $app['config']->set('barikoi.api_key', env('BARIKOI_API_KEY', 'bkoi_aba4dcd19f34e638f43d72fd857586e72927c07d7e21b7102cc9757cdd7ce5d6'));
        $app['config']->set('barikoi.base_url', env('BARIKOI_BASE_URL', 'https://barikoi.xyz/'));
    }
}

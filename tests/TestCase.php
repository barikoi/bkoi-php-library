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
        $app['config']->set('barikoi.api_key', env('BARIKOI_API_KEY', 'test_api_key'));
        $app['config']->set('barikoi.base_url', env('BARIKOI_BASE_URL', 'https://barikoi.xyz/v2/api'));
    }
}

<?php

namespace Barikoi\BarikoiApis;

use Illuminate\Support\ServiceProvider;

class BarikoiApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/barikoi.php';

        $this->publishes([$configPath => config_path('barikoi.php')], 'config');
        $this->mergeConfigFrom($configPath, 'barikoiapi');

        if ($this->app instanceof Laravel\Lumen\Application) {
            $this->app->configure('barikoiapi');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('barikoiapi', function ($app) {
            $config = isset($app['config']['services']['barikoiapi']) ? $app['config']['services']['barikoiapi'] : null;
            if (is_null($config)) {
                $config = $app['config']['barikoiapi'] ?: $app['config']['barikoiapi::config'];
            }

            $client = new BarikoiApiClient($config['api_key']);

            return $client;
        });

        $this->app->alias('barikoiapi', 'Barikoi\BarikoiApis\BarikoiApiClient');
    }

    public function provides() {
        return ['barikoiapi'];
    }
}

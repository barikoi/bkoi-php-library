<?php

namespace Barikoi\BarikoiApis;

use Illuminate\Support\ServiceProvider;

class BarikoiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/barikoi.php',
            'barikoi'
        );

        $this->app->singleton('barikoi', function ($app) {
            return new Barikoi(
                config('barikoi.api_key'),
                config('barikoi.base_url')
            );
        });

        $this->app->alias('barikoi', Barikoi::class);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/barikoi.php' => config_path('barikoi.php'),
            ], 'config');
        }
    }
}

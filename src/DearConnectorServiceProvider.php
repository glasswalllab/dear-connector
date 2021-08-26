<?php

namespace glasswalllab\dearconnector;

use Illuminate\Support\ServiceProvider;

class DearConnectorServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the main class to use with the facade
        $this->app->singleton('DearConnector', function () {
            return new DearConnector;
        });

        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'DearConnector');
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
<?php

namespace WiseServicePackage;

use Illuminate\Support\ServiceProvider;

class WiseServiceServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the service
        $this->app->singleton(WiseService::class, function ($app) {
            return new WiseService();
        });
    }

    public function boot()
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/wise.php' => config_path('wise.php'),
        ]);
    }
}

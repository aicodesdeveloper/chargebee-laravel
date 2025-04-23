<?php

namespace AicodesDeveloper\Chargebee;

use Illuminate\Support\ServiceProvider;
use AicodesDeveloper\Chargebee\Services\ChargebeeService;

class ChargebeeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Publish configuration file
        $this->publishes([
            __DIR__.'/../config/chargebee.php' => config_path('chargebee.php'),
        ], 'chargebee-config');

        // Register routes for webhooks
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Optional: Load migrations if you need to store subscription data
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge default config
        $this->mergeConfigFrom(
            __DIR__.'/../config/chargebee.php', 'chargebee'
        );

        // Register the main service
        $this->app->singleton('chargebee', function ($app) {
            return new ChargebeeService(
                config('chargebee.api_key'),
                config('chargebee.site'),
                config('chargebee.webhook_secret')
            );
        });
    }
}
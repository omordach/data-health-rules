<?php

namespace UnionImpact\DataHealthPoc;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DataHealthPocServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/data-health-poc.php', 'data-health-poc');
    }

    public function boot(): void
    {
        // migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');


        // console commands & publishable config

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\RunDataHealthCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/data-health-poc.php' => config_path('data-health-poc.php'),
            ], 'data-health-poc-config');
        }

        // optional metrics endpoint
        if (config('data-health-poc.metrics.enabled')) {
            Route::middleware(config('data-health-poc.metrics.middleware', []))
                ->get('/metrics/data-health-poc', Http\MetricsController::class);
        }
    }
}

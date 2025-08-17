<?php

namespace UnionImpact\DataHealthPoc;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DataHealthPocServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // no config for PoC
    }

    public function boot(): void
    {
        // migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\RunDataHealthCommand::class,
            ]);
        }

        // optional: small metrics endpoint
        Route::get('/metrics/data-health-poc', Http\MetricsController::class);
    }
}

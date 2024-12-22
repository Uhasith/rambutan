<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GenerateRambutanService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GenerateRambutanService::class, function ($app) {
            return new GenerateRambutanService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}

<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Socialite::extend('osu', function ($app) {
            $config = $app['config']['services.osu'];

            return Socialite::buildProvider(OsuSocialiteProvider::class, $config);
        });
        Paginator::useBootstrapFive();
    }
}

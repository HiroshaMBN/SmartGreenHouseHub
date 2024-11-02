<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register():void
    {
        Passport::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot():void
    {

        Passport::tokensExpireIn(now()->addMinutes(10));
        // Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addMinutes(10));
        Passport::personalAccessTokensExpireIn(now()->addMinutes(10));
    }
}

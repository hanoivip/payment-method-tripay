<?php

namespace Hanoivip\PaymentMethodTripay;

use Illuminate\Support\ServiceProvider;

class LibServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/hanoivip'),
            __DIR__.'/../config' => config_path(),
        ]);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadTranslationsFrom( __DIR__.'/../lang', 'hanoivip');
        //$this->mergeConfigFrom( __DIR__.'/../config/tsr.php', 'tsr');
        $this->loadViewsFrom(__DIR__ . '/../views', 'hanoivip');
    }
    
    public function register()
    {
        $this->commands([
        ]);
        $this->app->bind("TripayPaymentMethod", TripayMethod::class);
        $this->app->bind(IHelper::class, TripayApi::class);
        //$this->app->bind(IHelper::class, HelperTestSuccess::class);
        //$this->app->bind(IHelper::class, HelperTestDelay::class);
    }
}

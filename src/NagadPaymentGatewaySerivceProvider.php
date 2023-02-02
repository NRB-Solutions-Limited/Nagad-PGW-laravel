<?php

namespace Nrbsolution\nagad_payment_gateway;

use Illuminate\Support\ServiceProvider;

class NagadPaymentGatewaySerivceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'nagad_payment_gateway');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->mergeConfigFrom(__DIR__ . '/config/nagad_payment_gateway.php', 'nagad_payment_gateway');

        $this->publishes([
            __DIR__.'/config/nagad_payment_gateway.php' => config_path('nagad_payment_gateway.php'),
            __DIR__.'/resources/views' => resource_path('views/vendor/nagad_payment_gateway'),
        ]);
    }
}

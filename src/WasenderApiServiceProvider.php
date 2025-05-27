<?php

namespace WasenderApi;

use Illuminate\Support\ServiceProvider;

class WasenderApiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/wasenderapi.php' => config_path('wasenderapi.php'),
        ], 'wasenderapi-config');

        if (file_exists(__DIR__.'/../routes/webhooks.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/webhooks.php');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/wasenderapi.php', 'wasenderapi'
        );
        $this->app->singleton('wasenderapi.client', function ($app) {
            return new \WasenderApi\WasenderClient();
        });
    }
} 
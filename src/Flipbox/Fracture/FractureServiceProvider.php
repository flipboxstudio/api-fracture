<?php

namespace Flipbox\Fracture;

use Illuminate\Support\ServiceProvider;

class FractureServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/fracture.php' => config_path('fracture.php'),
        ], 'config');
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/fracture.php',
            'fracture'
        );

        $this->registerFractureResponseFactory();
    }

    /**
     * Register Fracture Response Factory.
     */
    protected function registerFractureResponseFactory()
    {
        $this->app->singleton([ResponseFactory::class => 'fracture.factory'], function ($app) {
            return new ResponseFactory(
                $this->app,
                $this->app->make('config'),
                $this->app->make('router')
            );
        });
    }
}

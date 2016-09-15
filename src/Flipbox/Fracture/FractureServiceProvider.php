<?php

namespace Flipbox\Fracture;

use Illuminate\Contracts\Http\Kernel;
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
        $this->prependFractureMiddlewareToKernel();
    }

    /**
     * Register Fracture Response Factory.
     */
    protected function registerFractureResponseFactory()
    {
        $this->app->singleton([Fracture::class => 'fracture.factory'], function ($app) {
            return new Fracture(
                $this->app,
                $this->app->make('config'),
                $this->app->make('router')
            );
        });
    }

    /**
     * Prepend Fracture Middleware to HTTPKernel.
     */
    protected function prependFractureMiddlewareToKernel()
    {
        $this->app->make(Kernel::class)->prependMiddleware(Http\Middleware\FractureMiddleware::class);
    }
}

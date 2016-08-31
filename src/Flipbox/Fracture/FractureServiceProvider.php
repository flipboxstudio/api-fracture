<?php

namespace Flipbox\Fracture;

use Flipbox\Fracture\Routing\Router;
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
        ]);

        $this->app->make(Kernel::class)->prependMiddleware(Middlewares\Request::class);
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

        $this->app->singleton(
            Middlewares\Request::class,
            Middlewares\Request::class
        );

        $this->app->singleton([ResponseFactory::class => 'fracture.factory'], function ($app) {
            return new ResponseFactory();
        });

        $this->app->singleton([Routing\Router::class => 'fracture.router'], function ($app) {
            return new Routing\Router($app->make('events'), $app);
        });
    }
}

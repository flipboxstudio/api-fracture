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
        ], 'config');

        $this->publishes([
            __DIR__.'/routes/fracture.php' => base_path('routes/fracture.php'),
        ], 'routes');

        $this->prependRequestMiddlewareToKernel();
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

        $this->registerFractureRouter();

        $this->mapFractureRoutes();
    }

    protected function prependRequestMiddlewareToKernel()
    {
        $this->app->make(Kernel::class)->prependMiddleware(Middlewares\Request::class);
    }

    protected function registerFractureResponseFactory()
    {
        $this->app->singleton([ResponseFactory::class => 'fracture.factory'], function ($app) {
            return new ResponseFactory();
        });
    }

    protected function registerFractureRouter()
    {
        $this->app->singleton([Routing\Router::class => 'fracture.router'], function ($app) {
            return new Routing\Router($app->make('events'), $app);
        });
    }

    protected function mapFractureRoutes()
    {
        $config = $this->app->make('config');

        Api::group([
            'middleware' => ['api'],
            'namespace' => $config->get('fracture.routes.namespace'),
            'subdomain' => $config->get('fracture.routes.subdomain'),
        ], function ($router) {
            require base_path('routes/fracture.php');
        });
    }
}

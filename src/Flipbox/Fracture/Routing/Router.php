<?php

namespace Flipbox\Fracture\Routing;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Router as IlluminateRouter;

class Router extends IlluminateRouter
{
    /**
     * Create a new Router instance.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @param \Illuminate\Container\Container         $container
     */
    public function __construct(Dispatcher $events, Container $container = null)
    {
        parent::__construct($events, $container);

        $kernel = $container->make(Kernel::class);

        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop;
        }, $kernel, $kernel);

        foreach ($propGetter('middlewareGroups') as $name => $middleware) {
            $this->middlewareGroup($name, $middleware);
        }

        foreach ($propGetter('routeMiddleware') as $name => $middleware) {
            $this->middleware($name, $middleware);
        }
    }

    /**
     * Create a new Route object.
     *
     * @param array|string $methods
     * @param string       $uri
     * @param mixed        $action
     *
     * @return \Illuminate\Routing\Route
     */
    protected function newRoute($methods, $uri, $action)
    {
        return (new Route($methods, $uri, $action))
                    ->setRouter($this)
                    ->setContainer($this->container);
    }
}

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

        $this->supplySelfPropertiesFromKernel($container->make(Kernel::class));
    }

    /**
     * Laravel routing needs this value, so we can get middleware per each routes.
     *
     * @param \Illuminate\Contracts\Http\Kernel $kernel
     *
     * @return void
     */
    protected function supplySelfPropertiesFromKernel(Kernel $kernel)
    {
        foreach ($this->getProtectedAttributeValue('middlewareGroups', $kernel) as $name => $middleware) {
            $this->middlewareGroup($name, $middleware);
        }

        foreach ($this->getProtectedAttributeValue('routeMiddleware', $kernel) as $name => $middleware) {
            $this->middleware($name, $middleware);
        }
    }

    /**
     * Get protected attribute from given name and object.
     *
     * @param string $attribute
     * @param mixed  $object
     *
     * @return mixed
     */
    protected function getProtectedAttributeValue(string $attribute, $object)
    {
        return Closure::bind(function ($attribute) {
            return $this->$attribute;
        }, $object, $object);
    }

    /**
     * Create a new Route object.
     *
     * @param array|string $methods
     * @param string       $uri
     * @param mixed        $action
     *
     * @return \Flipbox\Fracture\Routing\Route
     */
    protected function newRoute($methods, $uri, $action)
    {
        return (new Route($methods, $uri, $action))
                    ->setRouter($this)
                    ->setContainer($this->container);
    }
}

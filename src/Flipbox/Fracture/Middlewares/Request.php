<?php

namespace Flipbox\Fracture\Middlewares;

use Closure;
use Flipbox\Fracture\Handler;
use Illuminate\Pipeline\Pipeline;
use Flipbox\Fracture\Routing\Router;
use Flipbox\Fracture\ExceptionHandler;
use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandler;

class Request
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Exception handler instance.
     *
     * @var \Flipbox\Fracture\ExceptionHandler
     */
    protected $exception;

    /**
     * Router instance.
     *
     * @var \Flipbox\Fracture\Routing\Router
     */
    protected $router;

    /**
     * Create a new request middleware instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Flipbox\Fracture\ExceptionHandler           $exception
     * @param \Flipbox\Fracture\Routing\Router             $router
     */
    public function __construct(
        Container $app,
        ExceptionHandler $exception,
        Router $router
    ) {
        $this->app = $app;
        $this->exception = $exception;
        $this->router = $router;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        try {
            $route = $this->router->getRoutes()->match($request);

            $illuminateHandler = $this->app->make(IlluminateExceptionHandler::class);

            $this->app->instance(IlluminateExceptionHandler::class, new ExceptionHandler($illuminateHandler));

            $this->app->instance('router', $this->router);

            return $this->sendRequestThroughRouter($request);
        } catch (NotFoundHttpException $e) {
        }

        return $next($request);
    }

    /**
     * Send the given request through the middleware / router.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        return (new Pipeline($this->app))
                    ->send($request)
                    ->then($this->dispatchToRouter());
    }

    /**
     * Get the route dispatcher callback.
     *
     * @return \Closure
     */
    protected function dispatchToRouter()
    {
        return function ($request) {
            $this->app->instance('request', $request);

            return $this->router->dispatch($request);
        };
    }
}

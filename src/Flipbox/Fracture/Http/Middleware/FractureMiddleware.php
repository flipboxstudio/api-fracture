<?php

namespace Flipbox\Fracture\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Container\Container;
use Flipbox\Fracture\Exception\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;

class FractureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $app = Container::getInstance();

        if (Str::startsWith($request->getHost(), $app->make('config')->get('fracture.subdomain'))) {
            $illuminateHandler = $app->make(ExceptionHandler::class);

            $app->instance(ExceptionHandler::class, new Handler($illuminateHandler));
        }

        return $next($request);
    }
}

<?php

namespace Flipbox\Fracture\Routing;

use Flipbox\Fracture\Fracture;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection as IlluminateCollection;
use Illuminate\Contracts\Pagination\Paginator as IlluminatePaginator;
use Illuminate\Routing\ControllerDispatcher as IlluminateControllerDispatcher;

class ControllerDispatcher extends IlluminateControllerDispatcher
{
    /**
     * Dispatch a request to a given controller and method.
     *
     * @param \Illuminate\Routing\Route $route
     * @param mixed                     $controller
     * @param string                    $method
     *
     * @return mixed
     */
    public function dispatch(IlluminateRoute $route, $controller, $method)
    {
        $response = parent::dispatch($route, $controller, $method);

        if ($response instanceof IlluminatePaginator || $response instanceof IlluminateCollection) {
            $response = Fracture::responseCollection($response);
        } elseif ($response instanceof EloquentModel || is_array($response)) {
            $response = Fracture::responseItem($response);
        }

        return $response;
    }
}

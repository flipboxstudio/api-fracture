<?php

namespace Flipbox\Fracture\Routing;

use Flipbox\Fracture\Fracture;
use Illuminate\Support\Fluent;
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

        if ($this->responseIsACollection($response)) {
            $response = Fracture::responseCollection($response);
        } elseif ($this->responseIsAnItem($response)) {
            $response = Fracture::responseItem($response);
        }

        return $response;
    }

    /**
     * Determine if a response type is a collection.
     *
     * @param  mixed $response
     *
     * @return bool|boolean
     */
    protected function responseIsACollection($response) : bool
    {
        return $response instanceof IlluminatePaginator
            || $response instanceof IlluminateCollection;
    }

    /**
     * Determine if a response type is an item.
     *
     * @param  mixed $response
     *
     * @return bool|boolean
     */
    protected function responseIsAnItem($response) : bool
    {
        return $response instanceof EloquentModel
            || $response instanceof Fluent
            || is_array($response);
    }
}

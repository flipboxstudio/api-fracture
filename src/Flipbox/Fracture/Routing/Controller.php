<?php

namespace Flipbox\Fracture\Routing;

use Flipbox\Fracture\Fracture;
use Illuminate\Support\Fluent;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection as IlluminateCollection;
use Illuminate\Routing\Controller as IlluminateController;
use Illuminate\Contracts\Pagination\Paginator as IlluminatePaginator;

abstract class Controller extends IlluminateController
{
    /**
     * Execute an action on the controller.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        $response = call_user_func_array([$this, $method], $parameters);

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
     * @param mixed $response
     *
     * @return bool|bool
     */
    protected function responseIsACollection($response) : bool
    {
        return $response instanceof IlluminatePaginator
            || $response instanceof IlluminateCollection;
    }

    /**
     * Determine if a response type is an item.
     *
     * @param mixed $response
     *
     * @return bool|bool
     */
    protected function responseIsAnItem($response) : bool
    {
        return $response instanceof EloquentModel
            || $response instanceof Fluent
            || is_array($response);
    }
}

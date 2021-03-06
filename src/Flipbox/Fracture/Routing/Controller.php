<?php

namespace Flipbox\Fracture\Routing;

use Illuminate\Support\Fluent;
use Flipbox\Fracture\Facades\Fracture;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

        if ($this->responseIsAPaginator($response)) {
            $response = Fracture::responsePaginator($response);
        } elseif ($this->responseIsACollection($response)) {
            $response = Fracture::responseCollection($response);
        } elseif ($this->responseIsAnItem($response)) {
            $response = Fracture::responseItem($response);
        }

        return $response;
    }

    /**
     * Deterimine if a response type is an instance of Paginator.
     *
     * @param mixed $response
     *
     * @return bool
     */
    protected function responseIsAPaginator($response) : bool
    {
        return $response instanceof LengthAwarePaginator;
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

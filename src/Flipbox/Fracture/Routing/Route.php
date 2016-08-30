<?php

namespace Flipbox\Fracture\Routing;

use Illuminate\Routing\Route as IlluminateRoute;

class Route extends IlluminateRoute
{
    /**
     * Run the route action and return the response.
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function runController()
    {
        return (new ControllerDispatcher($this->container))->dispatch(
            $this,
            $this->getController(),
            $this->getControllerMethod()
        );
    }
}

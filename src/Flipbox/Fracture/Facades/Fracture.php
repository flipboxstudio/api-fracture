<?php

namespace Flipbox\Fracture\Facades;

use Illuminate\Support\Facades\Facade;

class Fracture extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'fracture.factory';
    }
}

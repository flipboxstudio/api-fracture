<?php

namespace Flipbox\Fracture\Transformers;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class FractureTransformer extends TransformerAbstract
{
    /**
     * Transform a resource.
     *
     * @param mixed $resource
     *
     * @return array
     */
    public function transform($resource) : array
    {
        $method = 'transformArray';

        if ($resource instanceof Model) {
            $method = 'transformEloquent';
        }

        return call_user_func_array([$this, $method], [$resource]);
    }

    /**
     * Transform an array resource.
     *
     * @param mixed $resource
     *
     * @return array
     */
    protected function transformArray($resource) : array
    {
        return (array) $resource;
    }

    /**
     * Transform an Eloquent resource.
     *
     * @param \Illuminate\Database\Eloquent\Model $resource
     *
     * @return array
     */
    protected function transformEloquent(Model $resource) : array
    {
        return $resource->toArray() + [
            'type' => $this->getResourceTypeFromEloquent($resource),
        ];
    }

    /**
     * Get resource type from eloquent.
     *
     * @param \Illuminate\Database\Eloquent\Model $resource
     *
     * @return string
     */
    protected function getResourceTypeFromEloquent(Model $resource) : string
    {
        return mb_strtolower(
            Arr::last(
                explode('\\', get_class($resource))
            )
        );
    }
}

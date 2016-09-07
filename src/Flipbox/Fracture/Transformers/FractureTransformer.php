<?php

namespace Flipbox\Fracture\Transformers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
        } elseif ($resource instanceof Collection) {
            $method = 'transformCollection';
        } elseif ($resource instanceof LengthAwarePaginator) {
            $method = 'transformPaginator';
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
        return $resource->toArray();
    }

    /**
     * Transform a Paginator resource.
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $resource
     *
     * @return array
     */
    protected function transformPaginator(LengthAwarePaginator $resource) : array
    {
        return $resource->toArray();
    }

    /**
     * Transform a Collection resource.
     *
     * @param \Illuminate\Support\Collection $resource
     *
     * @return array
     */
    protected function transformCollection(Collection $resource) : array
    {
        return $resource->toArray();
    }
}

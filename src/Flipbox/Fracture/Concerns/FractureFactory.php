<?php

namespace Flipbox\Fracture\Concerns;

use Exception;
use League\Fractal\Scope;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

trait FractureFactory
{
    /**
     * Create serializer.
     *
     * @param string $serializer
     *
     * @return mixed
     */
    protected function createSerializer($serializer)
    {
        return $this->app->make($serializer);
    }

    /**
     * Create a transformer.
     *
     * @param string|\League\Fractal\TransformerAbstract $transformer
     *
     * @return mixed
     */
    protected function createTransformer($transformer)
    {
        return is_string($transformer)
            ? $this->app->make($transformer)
            : $transformer;
    }

    /**
     * Create resource for item type resource.
     *
     * @param mixed $item
     *
     * @return \League\Fractal\Resource\Item
     */
    protected function createItemResource($item) : Item
    {
        return new Item($item, $this->resolveTransformerFromItem($item));
    }

    /**
     * Create resource for collection type resource.
     *
     * @param mixed $collection
     *
     * @return \League\Fractal\Resource\Collection
     */
    protected function createCollectionResource($collection) : Collection
    {
        return new Collection($collection, $this->resolveTransformerFromCollection($collection));
    }

    /**
     * Create a paginator resource.
     *
     * @param mixed $paginator
     *
     * @return \League\Fractal\Resource\Collection
     */
    protected function createPaginatorResource($paginator) : Collection
    {
        $resource = new Collection($paginator, $this->resolveTransformerFromPaginator($paginator));

        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        return $resource;
    }

    /**
     * Create fail resource.
     *
     * @param \Exception $e
     *
     * @return \League\Fractal\Resource\Item
     */
    protected function createErrorResource(Exception $e) : Item
    {
        return new Item($e, $this->resolveErrorTransformer($e));
    }

    /**
     * Build item scope.
     *
     * @param mixed $item
     *
     * @return \League\Fractal\Scope
     */
    public function item($item) : Scope
    {
        return $this->manager->createData(
            $this->createItemResource($item)
        );
    }

    /**
     * Create collection scope.
     *
     * @param mixed $collection
     *
     * @return \League\Fractal\Scope
     */
    public function collection($collection) : Scope
    {
        return $this->manager->createData(
            $this->createCollectionResource($collection)
        );
    }

    /**
     * Create a paginator scope.
     *
     * @param mixed $paginator
     *
     * @return \League\Fractal\Scope
     */
    public function paginator($paginator) : Scope
    {
        return $this->manager->createData(
            $this->createPaginatorResource($paginator)
        );
    }

    /**
     * Create error scope.
     *
     * @param \Exception $e
     *
     * @return \League\Fractal\Scope
     */
    public function error(Exception $e) : Scope
    {
        return $this->manager->createData(
            $this->createErrorResource($e)
        );
    }
}

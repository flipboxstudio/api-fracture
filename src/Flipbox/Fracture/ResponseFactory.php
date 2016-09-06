<?php

namespace Flipbox\Fracture;

use Exception;
use League\Fractal\Scope;
use League\Fractal\Manager;
use Illuminate\Http\JsonResponse;
use League\Fractal\Resource\Item;
use Illuminate\Support\Facades\App;
use League\Fractal\Resource\Collection;
use Flipbox\Fracture\Concerns\Resolvable;
use League\Fractal\Serializer\SerializerAbstract;

class ResponseFactory
{
    use Resolvable;

    /**
     * For a flag that someone has touced a message.
     * So we can override response message with this value.
     *
     * @var null|string
     */
    protected $message = null;

    /**
     * For a flag that someone has touced a success flag.
     * So we can override response success flag with this value.
     *
     * @var null|bool|boolean
     */
    protected $success = null;

    /**
     * A transformer of resource.
     *
     * @var null|string
     */
    protected $transformer = null;

    /**
     * Fractal manager.
     *
     * @var \League\Fractal\Manager
     */
    protected $manager;

    /**
     * Resource Serializer.
     *
     * @var \Flipbox\Fracture\Serializers\FractureSerializer
     */
    protected $serializer;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->manager = new Manager();

        $this->serializer = $this->createSerializer(
            $this->getDefaultSerializer()
        );

        $this->manager->setSerializer($this->serializer);
    }

    /**
     * Get fractal manager.
     *
     * @return \League\Fractal\Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set fractal manager.
     *
     * @param \League\Fractal\Manager $manager
     *
     * @return static
     */
    public function setManager(Manager $manager)
    {
        $this->manager = $manager;

        $this->setSerializer($this->serializer);

        return $this;
    }

    /**
     * Get serializer.
     *
     * @return \League\Fractal\Serializer\SerializerAbstract
     */
    public function getSerializer()
    {
        return $this->manager->getSerializer();
    }

    /**
     * Set serializer.
     *
     * @param \League\Fractal\Serializer\SerializerAbstract $serializer
     *
     * @return static
     */
    public function setSerializer(SerializerAbstract $serializer)
    {
        $this->serializer = $serializer;

        $this->manager->setSerializer($serializer);

        return $this;
    }

    /**
     * Set transformer for current resource.
     *
     * @param bool|boolean $create
     *
     * @return mixed
     */
    public function getTransformer($create = false)
    {
        return $create
            ? $this->createTransformer($this->transformer)
            : $this->transformer;
    }

    /**
     * Get transformer for current resource.
     *
     * @param string|\League\Fractal\TransformerAbstract $transformer
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Set response message.
     *
     * @param string $message
     *
     * @return static
     */
    public function setMessage(string $message)
    {
        $this->serializer->message = $this->message = $message;

        return $this;
    }

    /**
     * Set response success flag.
     *
     * @param bool|boolean $status
     *
     * @return static
     */
    public function setSuccess(bool $status)
    {
        $this->serializer->success = $this->success = $status;

        return $this;
    }

    /**
     * Transform a collection.
     *
     * @param mixed     $collection
     * @param string    $message
     * @param bool|bool $success
     * @param int|int   $status
     * @param array     $headers
     * @param int       $options
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseCollection(
        $collection,
        string $message = '',
        bool $success = true,
        int $status = 200,
        array $headers = [],
        int $options = 0
    ) : JsonResponse {
        $this->prepareSerializer($this->message ?? $message, $this->success ?? $success);

        return new JsonResponse($this->collection($collection)->toArray(), $status, $headers, $options);
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
     * Transform an item.
     *
     * @param mixed     $item
     * @param string    $message
     * @param bool|bool $success
     * @param int|int   $status
     * @param array     $headers
     * @param int       $options
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseItem(
        $item,
        string $message = '',
        bool $success = true,
        int $status = 200,
        array $headers = [],
        int $options = 0
    ) : JsonResponse {
        $this->prepareSerializer($this->message ?? $message, $this->success ?? $success);

        return new JsonResponse($this->item($item)->toArray(), $status, $headers, $options);
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
     * Generate failure resource response.
     *
     * @param string          $message
     * @param \Exception|null $e
     * @param int|int         $status
     * @param array           $headers
     * @param int             $options
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseError(
        string $message,
        Exception $e,
        int $status = 500,
        array $headers = [],
        int $options = 0
    ) : JsonResponse {
        $this->prepareErrorSerializer($message, false);

        return new JsonResponse($this->error($e)->toArray(), $status, $headers, $options);
    }

    /**
     * Create error scope
     *
     * @param  \Exception $e
     *
     * @return \League\Fractal\Scope
     */
    public function error(Exception $e) : Scope
    {
        return $this->manager->createData(
            $this->createErrorResource($e)
        );
    }

    /**
     * Create serializer.
     *
     * @param string $serializer
     *
     * @return mixed
     */
    protected function createSerializer($serializer)
    {
        return App::make($serializer);
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
            ? App::make($transformer)
            : $transformer;
    }

    /**
     * We set message and status request here.
     *
     * @param string $message
     * @param bool   $status
     */
    protected function prepareErrorSerializer(string $message, bool $status)
    {
        $this->serializer = $this->createSerializer(
            $this->getDefaultErrorSerializer()
        );

        $this->prepareSerializer($message, $status);

        $this->manager->setSerializer($this->serializer);
    }

    /**
     * We set message and status request here.
     *
     * @param string $message
     * @param bool   $status
     */
    protected function prepareSerializer(string $message, bool $status)
    {
        $this->serializer->status = $status;
        $this->serializer->message = $message;
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
     * Create fail resource.
     *
     * @param \Exception $e
     *
     * @return \League\Fractal\Resource\Item
     */
    protected function createErrorResource(Exception $e)
    {
        return new Item($e, $this->resolveErrorTransformer($e));
    }
}

<?php

namespace Flipbox\Fracture;

use Exception;
use League\Fractal\Manager;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use Flipbox\Fracture\Concerns\Resolvable;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Routing\Registrar;
use Flipbox\Fracture\Concerns\FractureFactory;
use League\Fractal\Serializer\SerializerAbstract;
use Illuminate\Contracts\Container\Container as ContainerContract;

class Fracture
{
    use Resolvable, FractureFactory;

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
     * @var null|bool|bool
     */
    protected $success = null;

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
     * A transformer of resource.
     *
     * @var null|string
     */
    protected $transformer = null;

    /**
     * Laravel application instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * Application configuration.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * The application router.
     *
     * @var \Illuminate\Contracts\Routing\Registrar
     */
    protected $router;

    /**
     * Class constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     * @param \Illuminate\Contracts\Config\Repository   $config
     * @param \Illuminate\Contracts\Routing\Registrar   $router
     */
    public function __construct(ContainerContract $app, Repository $config, Registrar $router)
    {
        $this->app = $app;
        $this->config = $config;
        $this->router = $router;

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
     * @param bool|bool $create
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
        $this->serializer->message = $this->message = Str::slug($message, '_');

        return $this;
    }

    /**
     * Set response success flag.
     *
     * @param bool|bool $status
     *
     * @return static
     */
    public function setSuccess(bool $status)
    {
        $this->serializer->success = $this->success = $status;

        return $this;
    }

    /**
     * Send a paginator response.
     *
     * @param mixed     $paginator
     * @param string    $message
     * @param bool|bool $success
     * @param int|int   $status
     * @param array     $headers
     * @param int|int   $options
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function responsePaginator(
        $paginator,
        string $message = '',
        bool $success = true,
        int $status = 200,
        array $headers = [],
        int $options = 0
    ) : JsonResponse {
        $this->prepareSerializer(($this->message ?? $message) ?: 'success', $this->success ?? $success);

        return new JsonResponse($this->paginator($paginator)->toArray(), $status, $headers, $options);
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
        $this->prepareSerializer(($this->message ?? $message) ?: 'success', $this->success ?? $success);

        return new JsonResponse($this->collection($collection)->toArray(), $status, $headers, $options);
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
        $this->prepareSerializer(($this->message ?? $message) ?: 'success', $this->success ?? $success);

        return new JsonResponse($this->item($item)->toArray(), $status, $headers, $options);
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
        $this->prepareErrorSerializer($message ?: 'error', false);

        return new JsonResponse($this->error($e)->toArray(), $status, $headers, $options);
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
        $this->setSuccess($status)
            ->setMessage($message);
    }
}

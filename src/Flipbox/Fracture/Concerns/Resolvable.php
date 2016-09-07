<?php

namespace Flipbox\Fracture\Concerns;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

trait Resolvable
{
    /**
     * Get current resolver for a collection.
     *
     * @param mixed $collection
     *
     * @return mixed
     */
    protected function resolveTransformerFromCollection($collection)
    {
        foreach ($collection as $item) {
            return $this->resolveTransformerFromItem($item);
        }
    }

    /**
     * Resolve Transformer from Paginator.
     *
     * @param mixed $paginator
     *
     * @return mixed
     */
    protected function resolveTransformerFromPaginator($paginator)
    {
        foreach ($paginator as $item) {
            return $this->resolveTransformerFromItem($item);
        }
    }

    /**
     * Get current resolver for an item.
     *
     * @param mixed $item
     *
     * @throws \Flipbox\Fracture\Exception\FractureException
     *
     * @return mixed
     */
    protected function resolveTransformerFromItem($item)
    {
        $transformer = $this->transformer
            ?? $this->getTransformerFromConfiguration($item)
            ?? $this->getTransformerFromRouteInformation()
            ?? $this->getDefaultTransformer()
            ?? $this->transformerNotFound();

        return $this->createTransformer($this->transformer = $transformer);
    }

    /**
     * Throwing transformer not found exception.
     *
     * @throws \Flipbox\Fracture\Exception\Fracture\Exception
     */
    protected function transformerNotFound()
    {
        throw new FractureException('Transformer not found');
    }

    /**
     * Get default transformer.
     *
     * @return null|string
     */
    protected function getDefaultTransformer()
    {
        return $this->config->get('fracture.default.transformer');
    }

    /**
     * Resolve error transformer.
     *
     * @param \Exception $e
     *
     * @return mixed
     */
    protected function resolveErrorTransformer(Exception $e)
    {
        $configKey = $this->determineConfigKeyFromObject($e);

        return $this->createTransformer(
            $this->config->get(
                "fracture.error_transformers.{$configKey}.class",
                $this->config->get('fracture.default.error_transformer')
            )
        );
    }

    /**
     * Get responsible transformer from route information.
     *
     * @return string|null
     */
    protected function getTransformerFromRouteInformation()
    {
        if (($route = $this->router->current()) !== null) {
            return Arr::get($route->getAction(), 'transformer');
        }
    }

    /**
     * Get responsible transformer from configuration mapping.
     *
     * @return string|null
     */
    protected function getTransformerFromConfiguration($item)
    {
        $configKey = $this->determineConfigKeyFromObject($item);

        return $this->config->get("fracture.transformers.{$configKey}.class");
    }

    /**
     * Get default serializer from configuration.
     *
     * @return string
     */
    protected function getDefaultSerializer() : string
    {
        return $this->config->get('fracture.default.serializer');
    }

    /**
     * Normalize config key.
     *
     * @param object $object
     *
     * @return string
     */
    protected function determineConfigKeyFromObject($object) : string
    {
        return (!is_object($object))
            ? 'array'
            : Str::snake(
                str_replace('\\', '', get_class($object))
            );
    }

    /**
     * Get default error serializer.
     *
     * @return string
     */
    protected function getDefaultErrorSerializer() : string
    {
        return $this->config->get('fracture.default.error_serializer');
    }

    /**
     * Resolve Message from Exception.
     *
     * @param \Exception $e
     *
     * @return string
     */
    protected function resolveMessageFromException(Exception $e) : string
    {
        return ($e instanceof HttpExceptionInterface)
            ? Str::slug(Arr::get(Response::$statusTexts, $e->getStatusCode(), ''), '_')
            : '';
    }
}

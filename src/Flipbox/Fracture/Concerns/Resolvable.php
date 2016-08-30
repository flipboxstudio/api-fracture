<?php

namespace Flipbox\Fracture\Concerns;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Flipbox\Fracture\FractureException;

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
     * Get current resolver for an item.
     *
     * @param mixed $item
     *
     * @throws \Flipbox\Fracture\FractureException
     *
     * @return mixed
     */
    protected function resolveTransformerFromItem($item)
    {
        $transformer = $this->getTransformerFromConfiguration($item)
            ?? $this->getTransformerFromRouteInformation()
            ?? $this->getDefaultTransformer();

        if ($transformer === null) {
            throw new FractureException('Transformer not found');
        }

        return $this->createTransformer($transformer);
    }

    /**
     * Get default transformer.
     *
     * @return null|string
     */
    protected function getDefaultTransformer()
    {
        return App::make('config')->get('fracture.default.transformer');
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
        $configKey = $this->normalizeConfigKey($e);

        return $this->createTransformer(
            App::make('config')->get(
                "fracture.error_transformers.{$configKey}.class",
                App::make('config')->get('fracture.default.error_transformer')
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
        return Arr::get(App::make('router')->current()->getAction(), 'transformer');
    }

    /**
     * Get responsible transformer from configuration mapping.
     *
     * @return string|null
     */
    protected function getTransformerFromConfiguration($item)
    {
        $configKey = $this->normalizeConfigKey($item);

        return App::make('config')->get("fracture.transformers.{$configKey}.class");
    }

    /**
     * Get default serializer from configuration.
     *
     * @return string
     */
    protected function getDefaultSerializer() : string
    {
        return App::make('config')->get('fracture.default.serializer');
    }

    /**
     * Normalize config key.
     *
     * @param object $object
     *
     * @return string
     */
    protected function normalizeConfigKey($object) : string
    {
        if (!is_object($object)) {
            return 'array';
        }

        return Str::snake(
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
        return App::make('config')->get('fracture.default.error_serializer');
    }
}

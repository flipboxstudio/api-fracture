<?php

namespace Flipbox\Fracture\Serializers;

use League\Fractal\Serializer\ArraySerializer;

class FractureSerializer extends ArraySerializer
{
    /**
     * Determine if current resource is a product of a successfully request.
     *
     * @var bool
     */
    public $success = true;

    /**
     * Message for current request.
     *
     * @var string
     */
    public $message = '';

    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data) : array
    {
        return $this->finalizeResource(
            $resourceKey,
            parent::collection($resourceKey, $data)
        );
    }

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function item($resourceKey, array $data) : array
    {
        return $this->finalizeResource(
            $resourceKey,
            parent::item($resourceKey, ['data' => $data])
        );
    }

    /**
     * Finalize resource to our standard.
     *
     * @param string $resourceKey
     * @param array  $resource
     *
     * @return array
     */
    protected function finalizeResource($resourceKey, array $resource) : array
    {
        return ['success' => $this->success] + $resource + ['message' => $this->message];
    }
}

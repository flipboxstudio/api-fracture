<?php

namespace Flipbox\Fracture\Serializers;

use League\Fractal\Serializer\DataArraySerializer;

class ErrorSerializer extends DataArraySerializer
{
    /**
     * Determine if current resource is a product of a successfully request.
     *
     * @var bool
     */
    public $success = false;

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
    public function collection($resourceKey, array $data)
    {
        return [
            'success' => $this->success,
            'data' => $data,
            'message' => $this->message,
        ];
    }

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        return [
            'success' => $this->success,
            'data' => $data,
            'message' => $this->message,
        ];
    }
}

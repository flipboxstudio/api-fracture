<?php

namespace Flipbox\Fracture\Transformers;

use App;
use Exception;
use League\Fractal\TransformerAbstract;

class ErrorTransformer extends TransformerAbstract
{
    /**
     * Transform an exception.
     *
     * @param \Exception $e
     *
     * @return array
     */
    public function transform(Exception $e) : array
    {
        return [
            'type' => 'error',
            'code' => (int) $e->getCode(),
        ] + $this->appendError($e);
    }

    /**
     * Append error information to response.
     *
     * @param \Exception $e
     *
     * @return array
     */
    protected function appendError(Exception $e) : array
    {
        if (App::make('config')->get('app.debug')) {
            return [
                'message' => $e->getMessage(),
                'trace' =>  explode("\n", $e->getTraceAsString()),
            ];
        }

        return [];
    }
}

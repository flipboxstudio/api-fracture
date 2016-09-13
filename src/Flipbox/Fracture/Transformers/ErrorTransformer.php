<?php

namespace Flipbox\Fracture\Transformers;

use Exception;
use Illuminate\Container\Container;
use League\Fractal\TransformerAbstract;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ErrorTransformer extends TransformerAbstract
{
    /**
     * Transform an exception.
     *
     * @param \Exception $exception
     *
     * @return array
     */
    public function transform(Exception $exception) : array
    {
        return [
            'status' => 'error',
            'code' => ($exception instanceof HttpExceptionInterface)
                ? (int) $exception->getStatusCode()
                : (int) $exception->getCode(),
        ] + $this->appendError($exception);
    }

    /**
     * Append error information to response.
     *
     * @param \Exception $exception
     *
     * @return array
     */
    protected function appendError(Exception $exception) : array
    {
        if (Container::getInstance()->make('config')->get('app.debug')) {
            return [
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        }

        return ['type' => get_class($exception)];
    }
}

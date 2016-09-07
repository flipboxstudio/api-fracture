<?php

namespace Flipbox\Fracture\Transformers;

use Exception;
use Illuminate\Container\Container;
use League\Fractal\TransformerAbstract;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

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
            'code' => ($exception instanceof HttpException)
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
        if (Container::getInstance()->make('config')->get('app.debug')
            && !$exception instanceof HttpException) {
            return [
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        } elseif ($exception instanceof PreconditionFailedHttpException) {
            if (($validationException = $exception->getPrevious()) instanceof ValidationException) {
                return [
                    'type' => 'validation_error',
                    'errors' => $validationException->validator->errors()->toArray(),
                ];
            }
        } elseif ($exception instanceof HttpException) {
            if (($message = $exception->getMessage()) !== '') {
                return [
                    'type' => 'http_exception',
                    'message' => $message,
                ];
            }
        }

        return ['type' => get_class($exception)];
    }
}

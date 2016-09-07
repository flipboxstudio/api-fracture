<?php

namespace Flipbox\Fracture\Exception;

use Exception;
use Illuminate\Http\Response;
use Flipbox\Fracture\Fracture;
use Illuminate\Container\Container;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandler;

class Handler implements IlluminateExceptionHandler
{
    /**
     * The parent Illuminate exception handler instance.
     *
     * @var \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected $parentHandler;

    /**
     * Create a new exception handler instance.
     *
     * @param \Illuminate\Contracts\Debug\ExceptionHandler $parentHandler
     */
    public function __construct(IlluminateExceptionHandler $parentHandler)
    {
        $this->parentHandler = $parentHandler;
    }

    /**
     * Report or log an exception.
     *
     * @param \Exception $exception
     */
    public function report(Exception $exception)
    {
        $this->parentHandler->report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Dingo\Api\Http\Request $request
     * @param \Exception              $exception
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function render($request, Exception $exception)
    {
        return $this->handle($exception);
    }

    /**
     * Render an exception to the console.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Exception                                        $exception
     *
     * @return mixed
     */
    public function renderForConsole($output, Exception $exception)
    {
        return $this->parentHandler->renderForConsole($output, $exception);
    }

    /**
     * Handle an exception if it has an existing handler.
     *
     * @param \Exception $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function handle(Exception $exception)
    {
        return Fracture::responseError(
            $this->getExceptionMessage($exception),
            $exception = $this->determineTypeOfException($exception),
            $this->getStatusCode($exception),
            $this->getHeaders($exception)
        );
    }

    /**
     * Get exception message.
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function getExceptionMessage(Exception $exception) : string
    {
        if (Container::getInstance()->make('config')->get('app.debug')
            && !$exception instanceof HttpExceptionInterface
        ) {
            return $exception->getMessage();
        }

        return $exception instanceof HttpExceptionInterface
            ? $exception->getMessage()
            : 'an_error_occured';
    }

    /**
     * Replace Laravel AuthenticationException with HttpException.
     *
     * @param \Exception $exception
     *
     * @return \Exception
     */
    protected function determineTypeOfException(Exception $exception) : Exception
    {
        if ($exception instanceof AuthenticationException) {
            $exception = new UnauthorizedHttpException(
                $exception->getMessage(),
                $exception
            );
        } elseif ($exception instanceof ValidationException) {
            $exception = new PreconditionFailedHttpException(
                $exception->getMessage(),
                $exception,
                412
            );
        }

        return $exception;
    }

    /**
     * Get the status code from the exception.
     *
     * @param \Exception $exception
     *
     * @return int
     */
    protected function getStatusCode(Exception $exception)
    {
        return $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;
    }

    /**
     * Get the headers from the exception.
     *
     * @param \Exception $exception
     *
     * @return array
     */
    protected function getHeaders(Exception $exception)
    {
        return $exception instanceof HttpExceptionInterface
            ? $exception->getHeaders()
            : [];
    }
}

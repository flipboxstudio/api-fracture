<?php

namespace Flipbox\Fracture;

use Exception;
use ReflectionFunction;
use Illuminate\Http\Response;
use Flipbox\Fracture\Fracture;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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
     *
     * @return void
     */
    public function __construct(IlluminateExceptionHandler $parentHandler)
    {
        $this->parentHandler = $parentHandler;
    }

    /**
     * Report or log an exception.
     *
     * @param \Exception $exception
     *
     * @return void
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
            $exception->getMessage(),
            $exception,
            $this->getStatusCode($exception),
            $this->getHeaders($exception)
        );
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
        return $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
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
        return $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];
    }

    /**
     * Get the exception status code.
     *
     * @param \Exception $exception
     * @param int        $defaultStatusCode
     *
     * @return int
     */
    protected function getExceptionStatusCode(Exception $exception, $defaultStatusCode = 500)
    {
        return ($exception instanceof HttpExceptionInterface) ? $exception->getStatusCode() : $defaultStatusCode;
    }
}

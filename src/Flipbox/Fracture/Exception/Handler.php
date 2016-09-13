<?php

namespace Flipbox\Fracture\Exception;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Flipbox\Fracture\Fracture;
use Illuminate\Container\Container;
use Symfony\Component\HttpFoundation\Response;
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
            $exception,
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
        return $exception instanceof HttpExceptionInterface
            ? $this->getHttpMessage($exception)
            : (
                Container::getInstance()->make('config')->get('app.debug')
                    ? $exception->getMessage()
                    : 'an_error_occured'
            );
    }

    /**
     * Get HTTP message.
     *
     * @param \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception
     *
     * @return string
     */
    protected function getHttpMessage(HttpExceptionInterface $exception) : string
    {
        return Str::slug(
            Arr::get(
                Response::$statusTexts,
                $exception->getStatusCode(),
                ''
            ),
            '_'
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

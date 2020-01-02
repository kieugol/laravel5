<?php

namespace App\Exceptions;

use App\Libraries\Api;
use ErrorException;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $response = [
            'message' => '',
            'data'    => '',
        ];
        // Customize response when exception is instance of ValidationException
        if ($exception instanceof ValidationException) {
            $message = null;
            foreach ($exception->errors() as $item) {
                $error = $item;
                if (is_array($item)) {
                    $error = $item[0];
                }
                $message .= empty($message) ? $error : '<br>'.$error;
            }
            $response['message'] = $message;
            return Api::response($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($exception instanceof NotFoundHttpException) {
            $response['message'] = 'Page not found';
            return Api::response($response, Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof HttpException) {
            $response['message'] = $exception->getMessage();
            return Api::response($response, $exception->getStatusCode());
        }

        if ($exception instanceof ErrorException) {
            $response['message'] = $exception->getMessage();
            return Api::response($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return parent::render($request, $exception);
    }
}

<?php

namespace App\Exceptions;

use App\Http\Controllers\BaseController;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception){

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception){

        if($exception instanceof UnauthorizedException) {
            return $this->handleUnauthorizedException($exception);

        } elseif ($exception instanceof AuthorizationException) {
            return $this->handleAuthorizationException($exception);

        }
        return parent::render($request, $exception);
    }

    protected function handleUnauthorizedException(UnauthorizedException $e){

         $data = [
            'message' => 'No sufficient priviledges to perform action.',
            'error' => $e->getMessage(),
        ];

        return $this->sendError($data);


    }


    protected function handleAuthorizationException(AuthorizationException $e){
        $responseData = [
            'message' => 'You do not have the right permissions to perform this action.',
            'error' => $e->getMessage(),
        ];

        return response()->json($responseData, 403);
    }

    public function sendError($error, $errorMessages = [], $code = 403){
        $response = [
            'success' => false,
            'data' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;

        }

        return response()->json($response, $code);

     }
}

<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
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
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($request->ajax()) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response([
                    'message' => 'Resource not found.',
                    'type'    => 'error',
                ], 404);
            }
            else if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response($e->validator->errors(), 422);
            }
        }
        
        if ($e instanceof \Illuminate\Session\TokenMismatchException) {            
             return redirect()
                    ->back()
                    ->withInput($request->except('password'))
                    ->with([
                        'message' => 'Validation Token was expired. Please try again',
                        'message-type' => 'danger'
                    ]);
        }

        return parent::render($request, $e);
    }
}

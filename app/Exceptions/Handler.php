<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];



    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception) : JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return response()->json(['error' => 'Unauthenticated.'], 401);
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof UnauthorizedException) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have the right roles or permissions.',
            ], Response::HTTP_FORBIDDEN); // 403
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'The requested resource could not be found.',
            ], Response::HTTP_NOT_FOUND);
        }



        if ($e instanceof ThrottleRequestsException) {
            return response()->json([
                'success' => false,
                'message' => 'Too many request attempts. Please wait before retrying.',
            ], Response::HTTP_TOO_MANY_REQUESTS); // 404
        }

        return parent::render($request, $e);
    }


}

<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {

            $statusCode = 500;
            $message = $exception->getMessage();
            $errors = null;

            if ($exception instanceof ValidationException) {
                $statusCode = 422;
                $message = app(\App\Services\Shared\MobileApiMessageS::class)->friendly($exception);
                $errors = $exception->errors();
            }

            elseif ($exception instanceof AuthenticationException) {
                $statusCode = 401;
                $message = app(\App\Services\Shared\MobileApiMessageS::class)->friendly($exception);
            }

            elseif ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
                $statusCode = 403;
                $message = app(\App\Services\Shared\MobileApiMessageS::class)->friendly($exception);
            }

            elseif ($exception instanceof ModelNotFoundException) {
                $statusCode = 404;
                $message = app(\App\Services\Shared\MobileApiMessageS::class)->friendly($exception);
            }

            elseif ($exception instanceof NotFoundHttpException) {
                $statusCode = 404;
                $message = 'Route not found';
            }

            elseif ($exception instanceof QueryException) {
                $statusCode = 500;
                $message = 'Something went wrong. Please try again.';

                if (config('app.debug')) {
                    $errors = [
                        'sql_message' => $exception->getMessage(),
                        'sql_code' => $exception->getCode(),
                    ];
                }
            }

            elseif ($exception instanceof HttpException) {
                $statusCode = $exception->getStatusCode();
                $message = app(\App\Services\Shared\MobileApiMessageS::class)->cleanMessage($exception->getMessage());
            }

            else {
                $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
                $message = 'Something went wrong. Please try again.';
            }

            $debugTrace = collect($exception->getTrace())
                ->map(function ($trace) {
                    return [
                        'file' => $trace['file'] ?? null,
                        'line' => $trace['line'] ?? null,
                        'class' => $trace['class'] ?? null,
                        'function' => $trace['function'] ?? null,
                    ];
                })
                ->filter(function ($trace) {
                    return !empty($trace['file']);
                })
                ->values();

            $projectTrace = $debugTrace
                ->filter(function ($trace) {
                    return str_contains($trace['file'], base_path('app'))
                        || str_contains($trace['file'], base_path('routes'))
                        || str_contains($trace['file'], base_path('resources'));
                })
                ->values();

            return response()->json([
                'success' => false,
                'message' => $message,

                'errors' => $errors,

                'debug' => config('app.debug') ? [
                    'exception' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),

                    // ✅ Tumhare project ki actual files
                    'project_trace' => $projectTrace->take(15),

                    // ✅ Full trace short
                    'trace' => $debugTrace->take(20),
                ] : null,

                'data' => null,
            ], $statusCode);
        }

        return parent::render($request, $exception);
    }
}
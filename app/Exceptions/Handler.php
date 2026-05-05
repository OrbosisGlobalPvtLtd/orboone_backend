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
                $message = 'Validation failed';
                $errors = $exception->errors();
            }

            if ($exception instanceof AuthenticationException) {
                $statusCode = 401;
                $message = 'Unauthenticated';
            }

            if ($exception instanceof ModelNotFoundException) {
                $statusCode = 404;
                $message = 'Record not found';
            }

            if ($exception instanceof NotFoundHttpException) {
                $statusCode = 404;
                $message = 'Route not found';
            }

            if ($exception instanceof QueryException) {
                $statusCode = 500;
                $message = 'Database Error';

                if (config('app.debug')) {
                    $errors = [
                        'sql_message' => $exception->getMessage(),
                        'sql_code' => $exception->getCode(),
                    ];
                }
            }

            if ($exception instanceof HttpException) {
                $statusCode = $exception->getStatusCode();
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
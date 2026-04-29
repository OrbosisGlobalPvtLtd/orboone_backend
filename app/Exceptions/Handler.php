<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
        'current_password',
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

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions in a clean JSON format.
     */
    protected function handleApiException($request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $exception->errors(),
                'data' => null,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => null,
                'data' => null,
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Requested record not found.',
                'errors' => null,
                'data' => null,
            ], Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'API route not found.',
                'errors' => null,
                'data' => null,
            ], Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'HTTP method not allowed for this endpoint.',
                'errors' => null,
                'data' => null,
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($exception instanceof ThrottleRequestsException) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'errors' => null,
                'data' => null,
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($exception instanceof PostTooLargeException) {
            return response()->json([
                'success' => false,
                'message' => 'Uploaded file is too large.',
                'errors' => null,
                'data' => null,
            ], Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        if ($exception instanceof HttpExceptionInterface) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: Response::$statusTexts[$exception->getStatusCode()] ?? 'HTTP error.',
                'errors' => null,
                'data' => null,
            ], $exception->getStatusCode());
        }

        Log::error('API Exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => config('app.debug')
                ? $exception->getMessage()
                : 'Internal server error.',
            'errors' => config('app.debug') ? [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ] : null,
            'data' => null,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
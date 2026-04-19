<?php

use App\Http\Responses\base\ErrorResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        apiPrefix: 'api',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return ErrorResponse::make(
                    message: 'Validation failed',
                    status: 422,
                    errorCode: 'VALIDATION_ERROR',
                    errors: $e->errors(),
                )->toResponse($request);
            }

            if ($e instanceof AuthenticationException) {
                return ErrorResponse::make(
                    message: 'Unauthenticated',
                    status: 401,
                    errorCode: 'UNAUTHENTICATED',
                )->toResponse($request);
            }

            if ($e instanceof NotFoundHttpException) {
                return ErrorResponse::make(
                    message: 'Resource not found',
                    status: 404,
                    errorCode: 'NOT_FOUND',
                )->toResponse($request);
            }

            if ($e instanceof HttpExceptionInterface) {
                return ErrorResponse::make(
                    message: $e->getMessage() ?: 'HTTP error',
                    status: $e->getStatusCode(),
                    errorCode: 'HTTP_ERROR',
                )->toResponse($request);
            }

            return ErrorResponse::make(
                message: config('app.debug') ? $e->getMessage() : 'Server error',
                status: 500,
                errorCode: 'SERVER_ERROR',
                errors: config('app.debug') ? ['exception' => get_class($e)] : null,
            )->toResponse($request);
        });
    })->create();

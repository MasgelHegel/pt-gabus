<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Resources\Api\V1\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    /**
     * Handle exceptions for API routes and return a JSON response.
     * Returns null if the exception should be handled by the default handler.
     */
    public static function handle(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->expectsJson() && ! str_starts_with($request->path(), 'api/')) {
            return null;
        }

        return match (true) {
            $e instanceof ValidationException      => self::handleValidation($e),
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException    => ApiResponse::notFound('Resource tidak ditemukan'),
            $e instanceof AuthenticationException  => ApiResponse::unauthorized('Token tidak valid atau sudah kadaluarsa'),
            $e instanceof AccessDeniedHttpException => ApiResponse::forbidden('Akses ditolak'),
            default                                => self::handleGeneric($e),
        };
    }

    private static function handleValidation(ValidationException $e): JsonResponse
    {
        return ApiResponse::error(
            'Data yang dikirim tidak valid',
            422,
            $e->errors()
        );
    }

    private static function handleGeneric(Throwable $e): JsonResponse
    {
        $isProduction = app()->environment('production');

        return ApiResponse::error(
            $isProduction ? 'Terjadi kesalahan pada server' : $e->getMessage(),
            500,
            $isProduction ? null : [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]
        );
    }
}

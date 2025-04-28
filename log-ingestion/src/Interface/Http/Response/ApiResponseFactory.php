<?php

declare(strict_types=1);

namespace Interface\Http\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiResponseFactory
{
    public static function success(array $data): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'data' => $data,
        ], JsonResponse::HTTP_OK);
    }

    public static function error(\Throwable $error): JsonResponse
    {
        $statusCode = $error instanceof HttpExceptionInterface ? $error->getStatusCode() : 500;
        return new JsonResponse([
            'status' => 'error',
            'message' => $error->getMessage(),
        ], $statusCode);
    }
}

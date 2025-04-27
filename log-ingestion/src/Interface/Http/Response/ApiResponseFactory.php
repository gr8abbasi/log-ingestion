<?php

declare(strict_types=1);

namespace Interface\Http\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponseFactory
{
    public static function success(array $data): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'data' => $data,
        ], JsonResponse::HTTP_OK);
    }

    public static function error(string $message, int $code): JsonResponse
    {
        return new JsonResponse([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }
}

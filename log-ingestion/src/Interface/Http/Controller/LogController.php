<?php

declare(strict_types=1);

namespace Interface\Http\Controller;

use Application\Log\Service\LogQueryServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Interface\Http\Request\LogRequest;
use Interface\Http\Response\ApiResponseFactory;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
readonly class LogController
{
    public function __construct(private LogQueryServiceInterface $logService)
    {
    }

    #[Route('/count', name: 'log_count', methods: ['GET'])]
    public function count(LogRequest $logRequest): JsonResponse
    {
        try {
            $filters = $logRequest->toFilters();

            $count = $this->logService->countLogs($filters);

            return ApiResponseFactory::success([
                'filters' => $filters->toArray(),
                'count' => $count,
            ]);
        } catch (\Throwable $error) {
            return ApiResponseFactory::error($error);
        }
    }
}

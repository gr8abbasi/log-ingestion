<?php

declare(strict_types=1);

namespace Interface\Http\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Interface\Http\Request\LogRequest;
use Interface\Http\Response\ApiResponseFactory;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Application\Log\Service\LogService;

#[AsController]
class LogController
{
    public function __construct(private readonly LogService $logService) {}

    #[Route('/count', name: 'log_count', methods: ['GET'])]
    public function count(LogRequest $logRequest): JsonResponse
    {
        $filters = $logRequest->toFilters();

        $count = $this->logService->countLogs($filters);

        return ApiResponseFactory::success([
            'filters' => $filters->toArray(),
            'count' => $count,
        ]);
    }
}

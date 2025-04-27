<?php

declare(strict_types=1);

namespace Application\Log\UseCase;

use Domain\Log\ValueObject\LogFilters;
use Domain\Log\Repository\LogEntryRepositoryInterface;

class CountLogs
{
    private LogEntryRepositoryInterface $repository;

    public function __construct(LogEntryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(LogFilters $filters): int
    {
        return $this->repository->countByFilters($filters);
    }
}

<?php

declare(strict_types=1);

namespace Application\Log\UseCase;

use Domain\Log\ValueObject\LogFilters;
use Domain\Log\Repository\LogEntryRepositoryInterface;

readonly class CountLogs
{
    public function __construct(private LogEntryRepositoryInterface $repository)
    {
    }

    public function execute(LogFilters $filters): int
    {
        return $this->repository->countByFilters($filters);
    }
}

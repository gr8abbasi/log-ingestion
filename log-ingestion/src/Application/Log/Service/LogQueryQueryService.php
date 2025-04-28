<?php

declare(strict_types=1);

namespace Application\Log\Service;

use Application\Log\UseCase\CountLogsInterface;
use Domain\Log\ValueObject\LogFilters;

readonly class LogQueryQueryService implements LogQueryServiceInterface
{
    public function __construct(private CountLogsInterface $countLogs)
    {
    }

    public function countLogs(LogFilters $filters): int
    {
        return $this->countLogs->execute($filters);
    }
}

<?php

declare(strict_types=1);

namespace Domain\Log\Repository;

use Domain\Log\ValueObject\LogFilters;
use Domain\Log\Entity\LogEntry;

interface LogEntryRepositoryInterface
{
    public function save(LogEntry $logEntry): void;

    public function flush(): void;

    public function countByFilters(LogFilters $filters): int;
}

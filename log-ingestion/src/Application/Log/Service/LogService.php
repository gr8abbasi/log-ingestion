<?php

declare(strict_types=1);

namespace Application\Log\Service;

use Application\Log\UseCase\CountLogs;
use Domain\Log\ValueObject\LogFilters;

readonly class LogService
{
    public function __construct(private CountLogs $countLogs)
    {
    }

    public function countLogs(LogFilters $filters): int
    {
        return $this->countLogs->execute($filters);
    }
}

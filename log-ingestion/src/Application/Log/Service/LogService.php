<?php

declare(strict_types=1);

namespace Application\Log\Service;

use Application\Log\UseCase\CountLogs;
use Domain\Log\ValueObject\LogFilters;

class LogService
{
    private CountLogs $countLogs;

    public function __construct(CountLogs $countLogs)
    {
        $this->countLogs = $countLogs;
    }

    public function countLogs(LogFilters $filters): int
    {
        return $this->countLogs->execute($filters);
    }
}

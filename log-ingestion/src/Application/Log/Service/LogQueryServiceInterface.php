<?php

declare(strict_types=1);

namespace Application\Log\Service;

use Domain\Log\ValueObject\LogFilters;

interface LogQueryServiceInterface
{
    public function countLogs(LogFilters $filters): int;
}

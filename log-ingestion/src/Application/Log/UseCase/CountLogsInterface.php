<?php

declare(strict_types=1);

namespace Application\Log\UseCase;

use Domain\Log\ValueObject\LogFilters;

interface CountLogsInterface
{
    public function execute(LogFilters $filters): int;
}

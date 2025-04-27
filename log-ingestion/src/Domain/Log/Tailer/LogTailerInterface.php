<?php

declare(strict_types=1);

namespace Domain\Log\Tailer;

use Domain\Log\ValueObject\LogEntry;

interface LogTailerInterface
{
    /**
     * @return \Generator<LogEntry>
     */
    public function tail(): \Generator;
}
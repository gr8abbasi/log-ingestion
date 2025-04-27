<?php

declare(strict_types=1);

namespace Application\Log\UseCase;

use Domain\Log\Repository\LogEntryRepositoryInterface;
use Infrastructure\Log\Parser\SimpleLogTailer;

class IngestLogs
{
    public function __construct(
        private readonly string $logFilePath,
        private readonly LogEntryRepositoryInterface $repository,
        private readonly SimpleLogTailer $parser
    ) {
    }

    public function execute(): void
    {
        foreach ($this->parser->parse($this->logFilePath) as $logEntry) {
            $this->repository->save($logEntry);
        }
    }
}
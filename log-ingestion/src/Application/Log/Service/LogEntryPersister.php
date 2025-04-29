<?php

declare(strict_types=1);

namespace Application\Log\Service;

use Application\Log\DTO\LogEntryMessageDto;
use Domain\Log\Entity\LogEntry;
use Domain\Log\Repository\LogEntryRepositoryInterface;

class LogEntryPersister
{
    private array $batch = [];

    public function __construct(
        private readonly LogEntryRepositoryInterface $logRepository,
        private readonly int $batchSize = 10
    ) {}

    public function process(LogEntryMessageDto $logDto): void
    {
        $logEntry = new LogEntry(
            $logDto->getService(),
            $logDto->getStartDate(),
            $logDto->getEndDate(),
            $logDto->getMethod(),
            $logDto->getPath(),
            $logDto->getStatusCode()
        );

        $this->batch[] = $logEntry;

        if (\count($this->batch) >= $this->batchSize) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        if (empty($this->batch)) {
            return;
        }

        foreach ($this->batch as $entry) {
            $this->logRepository->save($entry);
        }

        $this->logRepository->flush();
        $this->batch = [];
    }
}

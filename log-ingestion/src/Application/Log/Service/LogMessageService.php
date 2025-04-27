<?php

declare(strict_types=1);

namespace Application\Log\Service;

use Domain\Log\Entity\LogEntry;
use Domain\Log\Repository\LogEntryRepositoryInterface;

class LogMessageService
{
    private array $batch = [];

    public function __construct(
        private readonly LogEntryRepositoryInterface $logRepository,
        private readonly int $batchSize = 10
    ) {}

    public function process(array $data): void
    {
        $logEntry = new LogEntry(
            $data['service'] ?? 'unknown',
            new \DateTimeImmutable($data['startDate'] ?? 'now'),
            new \DateTimeImmutable($data['endDate'] ?? 'now'),
            $data['method'] ?? 'GET',
            $data['path'] ?? '/',
            (int)($data['statusCode'] ?? 200)
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

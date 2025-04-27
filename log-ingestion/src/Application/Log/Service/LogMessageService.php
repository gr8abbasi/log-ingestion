<?php

declare(strict_types=1);

namespace Application\Log\Service;

use Domain\Log\Entity\LogEntry;
use Doctrine\ORM\EntityManagerInterface;

class LogMessageService
{
    private EntityManagerInterface $entityManager;
    private array $batch = [];
    private int $batchSize;

    public function __construct(EntityManagerInterface $entityManager, int $batchSize = 10)
    {
        $this->entityManager = $entityManager;
        $this->batchSize = $batchSize;
    }

    public function process(array $data): void
    {
        $entry = new LogEntry(
            $data['service'] ?? 'unknown',
            new \DateTimeImmutable($data['startDate'] ?? 'now'),
            new \DateTimeImmutable($data['endDate'] ?? 'now'),
            $data['method'] ?? 'GET',
            $data['path'] ?? '/',
            (int)($data['statusCode'] ?? 200)
        );

        $this->batch[] = $entry;

        if (count($this->batch) >= $this->batchSize) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        if (empty($this->batch)) return;

        $this->entityManager->beginTransaction();
        try {
            foreach ($this->batch as $entry) {
                $this->entityManager->persist($entry);
            }
            $this->entityManager->flush();
            $this->entityManager->commit();
            echo "✅ Persisted " . count($this->batch) . " log entries.\n";
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        } finally {
            $this->batch = [];
        }
    }
}

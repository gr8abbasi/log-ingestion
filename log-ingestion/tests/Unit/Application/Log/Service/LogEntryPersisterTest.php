<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Log\Service;

use Application\Log\Service\LogEntryPersister;
use Domain\Log\Entity\LogEntry;
use Domain\Log\Repository\LogEntryRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LogEntryPersisterTest extends TestCase
{
    private LogEntryPersister $logMessageService;
    private MockObject&LogEntryRepositoryInterface $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = $this->createMock(LogEntryRepositoryInterface::class);

        $this->logMessageService = new LogEntryPersister(
            $this->mockRepository,
            1
        );
    }

    public function testProcessValidLogEntry()
    {
        $data = [
            'service' => 'test-service',
            'startDate' => '2025-04-01',
            'endDate' => '2025-04-01',
            'method' => 'GET',
            'path' => '/test',
            'statusCode' => 200
        ];

        $this->mockRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(LogEntry::class));

        $this->logMessageService->process($data);
    }

    public function testFlushLogs(): void
    {
        $entry = new LogEntry(
            'test-service',
            new \DateTimeImmutable('2025-04-01'),
            new \DateTimeImmutable('2025-04-01'),
            'GET',
            '/test',
            200
        );

        $this->mockRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (LogEntry $logEntry) use ($entry) {
                return $logEntry->getService() === $entry->getService()
                    && $logEntry->getStartDate() == $entry->getStartDate()
                    && $logEntry->getEndDate() == $entry->getEndDate()
                    && $logEntry->getMethod() === $entry->getMethod()
                    && $logEntry->getPath() === $entry->getPath()
                    && $logEntry->getStatusCode() === $entry->getStatusCode();
            }));

        $this->logMessageService->process([
            'service' => 'test-service',
            'startDate' => '2025-04-01',
            'endDate' => '2025-04-01',
            'method' => 'GET',
            'path' => '/test',
            'statusCode' => 200
        ]);

        $this->logMessageService->flush();
    }

}

<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Log\Service;

use Application\Log\DTO\LogEntryMessageDto;
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
        $logDto = new LogEntryMessageDto(
            'test-service',
            new \DateTimeImmutable('2024-01-01T00:00:00Z'),
            new \DateTimeImmutable('2024-01-01T00:01:00Z'),
            'POST',
            '/test',
            201
        );

        $this->mockRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(LogEntry::class));

        $this->logMessageService->process($logDto);
    }

    public function testFlushLogs(): void
    {
        $entry = new LogEntry(
            'test-service',
            new \DateTimeImmutable('2024-01-01T00:00:00Z'),
            new \DateTimeImmutable('2024-01-01T00:01:00Z'),
            'GET',
            '/test',
            200
        );

        $logDto = new LogEntryMessageDto(
            'test-service',
            new \DateTimeImmutable('2024-01-01T00:00:00Z'),
            new \DateTimeImmutable('2024-01-01T00:01:00Z'),
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

        $this->logMessageService->process($logDto);

        $this->logMessageService->flush();
    }

}

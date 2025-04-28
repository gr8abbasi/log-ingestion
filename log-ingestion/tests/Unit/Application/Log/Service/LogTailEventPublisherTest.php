<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Log\Service;

use Application\Log\Service\LogTailEventPublisher;
use Domain\Log\Tailer\LogTailerInterface;
use Domain\Log\Tailer\Event\LogLineReceivedEvent;
use Domain\Log\ValueObject\LogEntry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LogTailEventPublisherTest extends TestCase
{
    private LogTailerInterface&MockObject $tailer;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private LogTailEventPublisher $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tailer = $this->createMock(LogTailerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->service = new LogTailEventPublisher($this->tailer, $this->eventDispatcher);
    }

    public function testItDispatchesEventForEachLogLine(): void
    {
        $logEntry1 = new LogEntry('service-a', new \DateTimeImmutable(), new \DateTimeImmutable(), 'GET', '/health', 200);
        $logEntry2 = new LogEntry('service-b', new \DateTimeImmutable(), new \DateTimeImmutable(), 'POST', '/api', 201);

        $this->tailer
            ->expects($this->once())
            ->method('tail')
            ->willReturn($this->createGenerator([$logEntry1, $logEntry2]));

        $this->eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->callback(fn($event) => $event instanceof LogLineReceivedEvent && $event->logEntry === $logEntry1)],
                [$this->callback(fn($event) => $event instanceof LogLineReceivedEvent && $event->logEntry === $logEntry2)]
            );

        $this->service->execute();
    }

    private function createGenerator(array $entries): \Generator
    {
        foreach ($entries as $entry) {
            yield $entry;
        }
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Messaging\Subscriber;

use Application\Log\DTO\LogEntryMessageDto;
use Domain\Log\Tailer\Event\LogLineReceivedEvent;
use Domain\Log\ValueObject\LogEntry;
use Infrastructure\Log\Exception\KafkaPublisherException;
use Infrastructure\Log\Messaging\Subscriber\KafkaLogSubscriber;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Domain\Log\Messaging\MessagePublisherInterface;

class KafkaLogSubscriberTest extends TestCase
{
    private MockObject&MessagePublisherInterface $publisherMock;
    private KafkaLogSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publisherMock = $this->createMock(MessagePublisherInterface::class);
        $this->subscriber = new KafkaLogSubscriber($this->publisherMock);
    }

    public function testOnLogEntryReceivedPublishesCorrectMessage(): void
    {
        $logEntry = new LogEntry(
            service: 'my-service',
            startDate: new \DateTimeImmutable('2025-04-28T12:34:56Z'),
            endDate: new \DateTimeImmutable('2025-04-28T12:35:56Z'),
            method: 'GET',
            path: '/api/v1/resource',
            statusCode: 200
        );

        $event = new LogLineReceivedEvent($logEntry);

        $this->publisherMock->expects($this->once())
            ->method('publish')
            ->with(
                $this->equalTo('log.alerts'),
                $this->callback(function (LogEntryMessageDto $logDto) use ($logEntry) {
                    return $logDto->getService() === $logEntry->service
                        && $logDto->getStartDate() == $logEntry->startDate
                        && $logDto->getEndDate() == $logEntry->endDate
                        && $logDto->getMethod() === $logEntry->method
                        && $logDto->getPath() === $logEntry->path
                        && $logDto->getStatusCode() === $logEntry->statusCode;
                })
            );

        $this->subscriber->onLogEntryReceived($event);
    }

    public function testOnLogEntryReceivedThrowsKafkaPublisherException(): void
    {
        $this->expectException(KafkaPublisherException::class);

        $logEntry = new LogEntry(
            service: 'error-service',
            startDate: new \DateTimeImmutable(),
            endDate: new \DateTimeImmutable(),
            method: 'POST',
            path: '/fail',
            statusCode: 500
        );

        $event = new LogLineReceivedEvent($logEntry);

        $this->publisherMock->expects($this->once())
            ->method('publish')
            ->willThrowException(new \RuntimeException('Kafka unavailable'));

        $this->subscriber->onLogEntryReceived($event);
    }
}

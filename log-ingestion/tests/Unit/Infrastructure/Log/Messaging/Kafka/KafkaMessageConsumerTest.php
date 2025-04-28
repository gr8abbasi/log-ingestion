<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Messaging\Kafka;

use Application\Log\Service\LogEntryPersister;
use Application\Log\DLQ\DLQStrategyInterface;
use Infrastructure\Log\Messaging\Kafka\KafkaMessageConsumer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RdKafka\KafkaConsumer;
use RdKafka\Message;

class KafkaMessageConsumerTest extends TestCase
{
    private MockObject|KafkaConsumer $mockConsumer;
    private MockObject|LogEntryPersister $mockLogService;
    private MockObject|DLQStrategyInterface $mockDlqStrategy;
    private KafkaMessageConsumer $consumer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockConsumer = $this->createMock(KafkaConsumer::class);

        $this->mockLogService = $this->createMock(LogEntryPersister::class);

        $this->mockDlqStrategy = $this->createMock(DLQStrategyInterface::class);

        $this->consumer = new KafkaMessageConsumer(
            $this->mockConsumer,
            $this->mockLogService,
            $this->mockDlqStrategy,
            'test-topic'
        );
    }

    public function testConsumeProcessesMessageSuccessfully(): void
    {
        $mockMessage = $this->createMock(Message::class);
        $mockMessage->payload = json_encode([
            'service' => 'test-service',
            'startDate' => '2025-04-01',
            'endDate' => '2025-04-01',
            'method' => 'GET',
            'path' => '/test',
            'statusCode' => 200,
        ]);
        $mockMessage->err = 0;

        $this->mockConsumer->expects($this->exactly(2))
        ->method('consume')
            ->with(2000)
            ->willReturnOnConsecutiveCalls($mockMessage, null);

        $this->mockLogService->expects($this->once())
            ->method('process')
            ->with($this->callback(function ($payload) {
                return isset($payload['service']) && $payload['service'] === 'test-service';
            }));

        $this->mockConsumer->expects($this->once())
            ->method('commitAsync')
            ->with($mockMessage);

        $this->consumer->consume();
    }

    public function testConsumeHandlesKafkaError(): void
    {
        $mockMessage = $this->createMock(Message::class);
        $mockMessage->err = 1;

        $this->mockConsumer->expects($this->exactly(2))
        ->method('consume')
            ->with(2000)
            ->willReturnOnConsecutiveCalls($mockMessage, null);

        $this->expectOutputString("[Kafka Error] Some Kafka error\n");

        $this->consumer->consume();
    }

    public function testConsumeHandlesErrorAndSendsToDlq(): void
    {
        $mockMessage = $this->createMock(Message::class);
        $mockMessage->payload = json_encode([
            'service' => 'test-service',
            'startDate' => '2025-04-01',
            'endDate' => '2025-04-01',
            'method' => 'GET',
            'path' => '/test',
            'statusCode' => 200,
        ]);
        $mockMessage->err = 0;

        $this->mockLogService->expects($this->once())
            ->method('process')
            ->willThrowException(new \Exception("Processing error"));

        $this->mockDlqStrategy->expects($this->once())
            ->method('handle')
            ->with($mockMessage, $this->isInstanceOf(\Throwable::class));

        $this->consumer->consume();
    }
}

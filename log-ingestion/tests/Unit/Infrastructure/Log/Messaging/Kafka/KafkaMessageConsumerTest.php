<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Messaging\Kafka;

use Infrastructure\Log\Messaging\Kafka\KafkaMessageConsumer;
use Application\Log\Service\LogEntryPersister;
use Application\Log\DLQ\DLQStrategyInterface;
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

    //TODO: Implement Testcases below
    public function testConsumeProcessesValidMessage(): void
    {
        $this->assertTrue(True);
    }

    public function testConsumeHandlesKafkaError(): void
    {
        $this->assertTrue(True);
    }

    public function testConsumeHandlesProcessingFailureAndSendsToDlq(): void
    {
        $this->assertTrue(True);
    }
}

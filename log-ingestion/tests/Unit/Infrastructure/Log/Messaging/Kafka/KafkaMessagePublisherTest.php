<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Messaging\Kafka;

use Application\Log\DTO\LogEntryMessageDto;
use Infrastructure\Log\Messaging\Kafka\KafkaClientFactory;
use Infrastructure\Log\Messaging\Kafka\KafkaMessagePublisher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RdKafka\Producer;
use RdKafka\ProducerTopic;

class KafkaMessagePublisherTest extends TestCase
{
    private MockObject&KafkaClientFactory $mockClientFactory;
    private MockObject&Producer $mockProducer;
    private MockObject&ProducerTopic $mockProducerTopic;
    private KafkaMessagePublisher $publisher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClientFactory = $this->createMock(KafkaClientFactory::class);
        $this->mockProducer = $this->createMock(Producer::class);
        $this->mockProducerTopic = $this->createMock(ProducerTopic::class);

        $this->mockClientFactory->expects($this->once())
            ->method('createProducer')
            ->with('localhost')
            ->willReturn($this->mockProducer);

        $this->publisher = new KafkaMessagePublisher($this->mockClientFactory, 'localhost');
    }

    public function testPublishSendsMessageToTopic(): void
    {
        $topicName = 'test-topic';
        $logDto = new LogEntryMessageDto(
            'test-service',
            new \DateTimeImmutable('2025-04-01'),
            new \DateTimeImmutable('2025-04-01'),
            'GET',
            '/test',
            200
        );

        $this->mockProducer->expects($this->once())
            ->method('newTopic')
            ->with($topicName)
            ->willReturn($this->mockProducerTopic);

        $this->mockProducerTopic->expects($this->once())
            ->method('produce')
            ->with(
                RD_KAFKA_PARTITION_UA,
                0,
                json_encode($logDto->toArray())
            );

        $this->mockProducer->expects($this->once())
            ->method('poll')
            ->with(0);

        $this->publisher->publish($topicName, $logDto);
    }

    public function testPublishHandlesErrorAndSendsToDlq(): void
    {
        $topicName = 'test-topic';
        $payload = new LogEntryMessageDto(
            'test-service',
            new \DateTimeImmutable('2025-04-01'),
            new \DateTimeImmutable('2025-04-01'),
            'GET',
            '/test',
            200
        );
        $errorMessage = 'Kafka error occurred';

        $mockMainTopic = $this->createMock(ProducerTopic::class);
        $mockDlqTopic = $this->createMock(ProducerTopic::class);

        $this->mockProducer->expects($this->exactly(2))
            ->method('newTopic')
            ->withConsecutive(
                [$this->equalTo($topicName)],
                [$this->equalTo('log.alerts.dlq')]
            )
            ->willReturnOnConsecutiveCalls($mockMainTopic, $mockDlqTopic);

        $mockMainTopic->expects($this->once())
            ->method('produce')
            ->willThrowException(new \RuntimeException($errorMessage));

        $mockDlqTopic->expects($this->once())
            ->method('produce')
            ->with(
                RD_KAFKA_PARTITION_UA,
                0,
                $this->callback(function ($message) use ($topicName, $payload, $errorMessage) {
                    $decoded = json_decode($message, true);
                    return $decoded['originalTopic'] === $topicName
                        && $decoded['error'] === $errorMessage
                        && $decoded['failedMessage'] === $payload->toArray();
                })
            );

        $this->mockProducer->expects($this->exactly(1))
            ->method('poll')
            ->with(0);

        $this->publisher->publish($topicName, $payload);
    }

}

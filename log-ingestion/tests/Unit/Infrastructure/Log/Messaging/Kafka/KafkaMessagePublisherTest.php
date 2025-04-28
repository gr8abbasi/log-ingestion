<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Messaging\Kafka;

use Infrastructure\Log\Messaging\Kafka\KafkaMessagePublisher;
use Infrastructure\Log\Messaging\Kafka\KafkaClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RdKafka\Producer;
use RdKafka\ProducerTopic;

class KafkaMessagePublisherTest extends TestCase
{
    private MockObject|KafkaClientFactory $mockClientFactory;
    private MockObject|Producer $mockProducer;
    private MockObject|ProducerTopic $mockProducerTopic;
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
        $payload = ['key' => 'value'];

        $this->mockProducer->expects($this->once())
            ->method('newTopic')
            ->with($topicName)
            ->willReturn($this->mockProducerTopic);

        $this->mockProducerTopic->expects($this->once())
            ->method('produce')
            ->with(
                $this->equalTo(RD_KAFKA_PARTITION_UA),
                $this->equalTo(0),
                $this->equalTo(json_encode($payload))
            );

        $this->mockProducer->expects($this->once())
            ->method('poll')
            ->with(0);

        $this->publisher->publish($topicName, $payload);
    }

    public function testPublishHandlesErrorAndSendsToDlq(): void
    {

        $topicName = 'test-topic';
        $payload = ['key' => 'value'];
        $errorMessage = 'Kafka error occurred';


        $this->mockProducer->expects($this->once())
            ->method('newTopic')
            ->with($topicName)
            ->willReturn($this->mockProducerTopic);


        $this->mockProducerTopic->expects($this->once())
            ->method('produce')
            ->willThrowException(new \RuntimeException($errorMessage));

        $this->mockProducer->expects($this->once())
            ->method('newTopic')
            ->with('log.alerts.dlq')
            ->willReturn($this->mockProducerTopic);

        $this->mockProducerTopic->expects($this->once())
            ->method('produce')
            ->with(
                $this->equalTo(RD_KAFKA_PARTITION_UA),
                $this->equalTo(0),
                $this->callback(function ($message) use ($topicName, $payload, $errorMessage) {
                    $decoded = json_decode($message, true);
                    return $decoded['originalTopic'] === $topicName
                        && $decoded['error'] === $errorMessage
                        && $decoded['failedMessage'] === $payload;
                })
            );

        $this->publisher->publish($topicName, $payload);
    }
}

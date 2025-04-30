<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Messaging\Kafka;

use Infrastructure\Log\Messaging\Kafka\KafkaDlqService;
use Infrastructure\Log\Messaging\Kafka\KafkaTopicInterface;
use Infrastructure\Log\Messaging\Kafka\KafkaTopicFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RdKafka\Message;
use RdKafka\Producer;

class KafkaDlqServiceTest extends TestCase
{
    private MockObject&KafkaTopicFactoryInterface $topicFactory;
    private MockObject&KafkaTopicInterface $topic;
    private MockObject&Producer $producer;
    private KafkaDlqService $dlqService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->topicFactory = $this->createMock(KafkaTopicFactoryInterface::class);
        $this->topic = $this->createMock(KafkaTopicInterface::class);
        $this->producer = $this->createMock(Producer::class);

        $this->topicFactory
            ->method('createTopic')
            ->with('log.alerts.dlq')
            ->willReturn($this->topic);

        $this->dlqService = new KafkaDlqService(
            topicFactory: $this->topicFactory,
            producer: $this->producer,
            dlqTopic: 'log.alerts.dlq'
        );
    }

    public function testHandleSendsToDlq(): void
    {
        $message = new Message();
        $message->payload = 'test-payload';
        $message->topic_name = 'test-topic';

        $exception = new \RuntimeException('Test Exception Message');

        $this->topic->expects($this->once())
            ->method('produce')
            ->with(
                RD_KAFKA_PARTITION_UA,
                0,
                $this->callback(function (string $jsonPayload) use ($message, $exception) {
                    $decoded = json_decode($jsonPayload, true);
                    return $decoded['error'] === $exception->getMessage()
                        && $decoded['payload'] === $message->payload
                        && $decoded['topic'] === $message->topic_name
                        && isset($decoded['timestamp']);
                })
            );

        $this->producer->expects($this->once())
            ->method('poll')
            ->with(0);

        $this->dlqService->handle($message, $exception);
    }
}

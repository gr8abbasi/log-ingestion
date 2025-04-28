<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Messaging\Kafka;

use Infrastructure\Log\Messaging\Kafka\KafkaDlqService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RdKafka\Message;
use RdKafka\Producer;
use RdKafka\Topic;
use Exception;

class KafkaDlqServiceTest extends TestCase
{
    private MockObject&Producer $mockProducer;
    private MockObject&Topic $mockTopic;
    private KafkaDlqService $kafkaDlqService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockProducer = $this->createMock(Producer::class);

        $this->mockTopic = $this->createMock(Topic::class);

        $this->mockProducer->expects($this->any())
            ->method('newTopic')
            ->willReturn($this->mockTopic);

        $this->kafkaDlqService = new KafkaDlqService('kafka:9092');
    }

    public function testHandle()
    {
        $mockMessage = $this->createMock(Message::class);
        $mockMessage->payload = 'test-payload';
        $mockMessage->topic_name = 'test-topic';

        $mockException = $this->createMock(Exception::class);  // Mock Exception directly
//        $mockException->method('getMessage')
//            ->willReturn('Test Exception Message');

        $this->mockTopic->expects($this->once())
            ->method('produce')
            ->with(
                $this->equalTo(RD_KAFKA_PARTITION_UA),
                $this->equalTo(0),
                $this->callback(function ($payload) {
                    $expectedPayload = [
                        'error' => 'Test Exception Message',
                        'payload' => 'test-payload',
                        'topic' => 'test-topic',
                        'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                    ];
                    $decodedPayload = json_decode($payload, true);
                    return $decodedPayload === $expectedPayload;
                })
            );

        $this->mockProducer->expects($this->once())
            ->method('poll')
            ->with($this->equalTo(0));

        $this->kafkaDlqService->handle($mockMessage, $mockException);
    }
}

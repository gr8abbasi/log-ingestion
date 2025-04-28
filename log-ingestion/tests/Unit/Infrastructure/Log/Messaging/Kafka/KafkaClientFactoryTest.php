<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Messaging\Kafka;

use Infrastructure\Log\Messaging\Kafka\KafkaClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;
use RdKafka\Conf;

class KafkaClientFactoryTest extends TestCase
{
    private MockObject&Conf $mockConf;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockConf = $this->createMock(Conf::class);

        $this->mockConf->expects($this->any())
            ->method('set')
            ->willReturnSelf();
    }

    public function testCreateProducer()
    {
        $brokers = 'kafka:9092';

        $this->mockConf->expects($this->once())
            ->method('set')
            ->with('metadata.broker.list', $brokers);

        $producer = new Producer($this->mockConf);

        $createdProducer = KafkaClientFactory::createProducer($brokers);

        $this->assertInstanceOf(Producer::class, $createdProducer);
    }

    public function testCreateConsumer()
    {
        $brokers = 'kafka:9092';

        $this->mockConf->expects($this->exactly(5))
            ->method('set')
            ->withConsecutive(
                ['metadata.broker.list', $brokers],
                ['group.id', 'log-consumer-group'],
                ['auto.offset.reset', 'earliest'],
                ['socket.timeout.ms', '30000'],
                ['session.timeout.ms', '6000']
            );

        $consumer = new KafkaConsumer($this->mockConf);

        $createdConsumer = KafkaClientFactory::createConsumer($brokers);

        $this->assertInstanceOf(KafkaConsumer::class, $createdConsumer);
    }
}

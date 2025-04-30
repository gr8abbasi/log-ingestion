<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Messaging\Kafka;

use Infrastructure\Log\Messaging\Kafka\KafkaClientFactory;
use PHPUnit\Framework\TestCase;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;

class KafkaClientFactoryTest extends TestCase
{
    public function testCreateProducer(): void
    {
        $brokers = 'kafka:9092';

        $producer = (new KafkaClientFactory())->createProducer($brokers);

        $this->assertInstanceOf(Producer::class, $producer);
    }

    public function testCreateConsumer(): void
    {
        $brokers = 'kafka:9092';

        $consumer = (new KafkaClientFactory())->createConsumer($brokers);

        $this->assertInstanceOf(KafkaConsumer::class, $consumer);
    }
}

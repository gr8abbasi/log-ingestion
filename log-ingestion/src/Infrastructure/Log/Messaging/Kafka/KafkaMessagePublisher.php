<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Kafka;

use Domain\Log\Messaging\MessagePublisherInterface;
use RdKafka\Producer;
use RdKafka\ProducerTopic;

class KafkaMessagePublisher implements MessagePublisherInterface
{
    private Producer $producer;
    private array $topics = [];

    public function __construct(
        KafkaClientFactory $clientFactory,
        string $brokers,
        private string $dlqTopic = 'log.alerts.dlq'
    ) {
        $this->producer = $clientFactory::createProducer($brokers);
    }

    public function publish(string $topic, array $payload): void
    {
        $message = json_encode($payload);

        try {
            $this->getTopic($topic)->produce(RD_KAFKA_PARTITION_UA, 0, $message);
            $this->producer->poll(0);
        } catch (\Throwable $e) {
            // Send to DLQ
            $errorPayload = [
                'originalTopic' => $topic,
                'error' => $e->getMessage(),
                'failedMessage' => $payload,
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
            ];
            $this->getTopic($this->dlqTopic)->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($errorPayload));
        }
    }

    private function getTopic(string $name): ProducerTopic
    {
        if (!isset($this->topics[$name])) {
            $this->topics[$name] = $this->producer->newTopic($name);
        }

        return $this->topics[$name];
    }
}
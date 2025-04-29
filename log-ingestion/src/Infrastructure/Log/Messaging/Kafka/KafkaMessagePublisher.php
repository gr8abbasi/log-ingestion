<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Kafka;

use Application\Log\DTO\LogEntryMessageDto;
use Application\Log\DTO\LogEntryDLQDto;
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

    public function publish(string $topic, LogEntryMessageDto $logDto): void
    {
        $message = json_encode($logDto->toArray());

        try {
            $this->getTopic($topic)->produce(RD_KAFKA_PARTITION_UA, 0, $message);
            $this->producer->poll(0);
        } catch (\Throwable $e) {
            $this->publishToDLQ($topic, $e, $logDto);
        }
    }

    private function publishToDLQ(string $originalTopic, \Throwable $e, LogEntryMessageDto $logDto): void
    {
        $errorPayload = new LogEntryDLQDto(
            $originalTopic,
            $e->getMessage(),
            $logDto->toArray(),
            (new \DateTimeImmutable())->format(DATE_ATOM)
        );

        $message = json_encode($errorPayload->toArray());

        try {
            $this->getTopic($this->dlqTopic)->produce(RD_KAFKA_PARTITION_UA, 0, $message);
            $this->producer->poll(0);
        } catch (\Throwable $e) {
            echo "[DLQ Error] Failed to publish to DLQ: " . $e->getMessage() . "\n";
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

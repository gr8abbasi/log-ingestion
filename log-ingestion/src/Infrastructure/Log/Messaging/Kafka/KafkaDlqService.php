<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Kafka;

use Application\Log\DLQ\DLQStrategyInterface;
use RdKafka\Message;
use RdKafka\Producer;

class KafkaDlqService implements DLQStrategyInterface
{
    private Producer $producer;
    private string $dlqTopic;

    public function __construct(string $brokers, string $dlqTopic = 'log.alerts.dlq')
    {
        $conf = new \RdKafka\Conf();
        $conf->set('metadata.broker.list', $brokers);
        $this->producer = new Producer($conf);
        $this->dlqTopic = $dlqTopic;
    }

    public function handle(Message $message, \Throwable $exception): void
    {
        $payload = [
            'error' => $exception->getMessage(),
            'payload' => $message->payload,
            'topic' => $message->topic_name,
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $topic = $this->producer->newTopic($this->dlqTopic);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($payload));
        $this->producer->poll(0);

        echo "📤 Sent to DLQ: {$this->dlqTopic}\n";
    }
}

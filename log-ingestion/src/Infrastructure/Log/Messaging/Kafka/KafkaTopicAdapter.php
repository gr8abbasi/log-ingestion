<?php

namespace Infrastructure\Log\Messaging\Kafka;

use RdKafka\Topic;

class KafkaTopicAdapter implements KafkaTopicInterface
{
    public function __construct(private Topic $topic)
    {
    }

    public function produce(int $partition, int $msgFlags, string $payload): void
    {
        $this->topic->produce($partition, $msgFlags, $payload);
    }
}

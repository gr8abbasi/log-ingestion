<?php

namespace Infrastructure\Log\Messaging\Kafka;

use RdKafka\Producer;

class KafkaTopicFactory implements KafkaTopicFactoryInterface
{
    public function __construct(private Producer $producer)
    {
    }

    public function createTopic(string $topicName): KafkaTopicInterface
    {
        return new KafkaTopicAdapter($this->producer->newTopic($topicName));
    }
}
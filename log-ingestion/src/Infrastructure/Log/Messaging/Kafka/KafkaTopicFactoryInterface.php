<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Kafka;

interface KafkaTopicFactoryInterface
{
    public function createTopic(string $topicName): KafkaTopicInterface;
}
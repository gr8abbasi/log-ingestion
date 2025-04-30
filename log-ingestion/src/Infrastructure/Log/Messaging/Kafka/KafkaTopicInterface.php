<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Kafka;

interface KafkaTopicInterface
{
    public function produce(int $partition, int $msgFlags, string $payload): void;
}

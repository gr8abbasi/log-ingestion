<?php

declare(strict_types=1);

namespace Domain\Log\Messaging;

interface MessagePublisherInterface
{
    public function publish(string $topic, array $payload): void;
}
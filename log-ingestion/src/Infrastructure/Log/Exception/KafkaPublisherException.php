<?php

declare(strict_types=1);

namespace Infrastructure\Log\Exception;

use Exception;

class KafkaPublisherException extends Exception
{
    public static function fromPublishingFailure(string $error, \Throwable $previous = null): self
    {
        return new self("Failed to publish Kafka message: $error", 0, $previous);
    }
}

<?php

declare(strict_types=1);

namespace Application\Log\DTO;

final class LogEntryDLQDto
{
    public const  ORIGINAL_TOPIC = 'originalTopic';
    public const ERROR = 'error';
    public const FAILED_MESSAGE = 'failedMessage';
    public const TIMESTAMP = 'timestamp';

    public function __construct(
        private readonly string $originalTopic,
        private readonly string $error,
        private readonly array  $failedMessage,
        private readonly string $timestamp
    )
    {
    }

    public function getOriginalTopic(): string
    {
        return $this->originalTopic;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getFailedMessage(): array
    {
        return $this->failedMessage;
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data[self::ORIGINAL_TOPIC],
            $data[self::ERROR],
            $data[self::FAILED_MESSAGE],
            $data[self::TIMESTAMP]
        );
    }

    public function toArray(): array
    {
        return [
            self::ORIGINAL_TOPIC => $this->originalTopic,
            self::ERROR => $this->error,
            self::FAILED_MESSAGE => $this->failedMessage,
            self::TIMESTAMP => $this->timestamp,
        ];
    }
}

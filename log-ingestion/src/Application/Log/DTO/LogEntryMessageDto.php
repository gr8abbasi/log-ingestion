<?php

declare(strict_types=1);

namespace Application\Log\DTO;

use Domain\Log\Enum\LogEntry;

final readonly class LogEntryMessageDto
{
    public function __construct(
        private string             $service,
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
        private string             $method,
        private string             $path,
        private int                $statusCode
    )
    {
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function toArray(): array
    {
        return [
            LogEntry::SERVICE->value => $this->service,
            LogEntry::START_DATE->value => $this->startDate->format(DATE_ATOM),
            LogEntry::END_DATE->value => $this->endDate->format(DATE_ATOM),
            LogEntry::METHOD->value => $this->method,
            LogEntry::PATH->value => $this->path,
            LogEntry::STATUS_CODE->value => $this->statusCode,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data[LogEntry::SERVICE->value],
            new \DateTimeImmutable($data[LogEntry::START_DATE->value]),
            new \DateTimeImmutable($data[LogEntry::END_DATE->value]),
            $data[LogEntry::METHOD->value],
            $data[LogEntry::PATH->value],
            $data[LogEntry::STATUS_CODE->value]
        );
    }
}
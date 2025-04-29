<?php

declare(strict_types=1);

namespace Domain\Log\Entity;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class LogEntry
{
    private UuidInterface $uuid;

    public function __construct(
        private readonly string             $service,
        private readonly \DateTimeImmutable $startDate,
        private readonly \DateTimeImmutable $endDate,
        private readonly string             $method,
        private readonly string             $path,
        private readonly int                $statusCode
    )
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
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
}
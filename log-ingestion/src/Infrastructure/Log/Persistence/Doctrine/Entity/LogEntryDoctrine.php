<?php

declare(strict_types=1);

namespace Infrastructure\Log\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'log_entries')]
class LogEntryDoctrine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $uuid;

    #[ORM\Column(length: 100)]
    private string $service;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $endDate;

    #[ORM\Column(length: 10)]
    private string $method;

    #[ORM\Column(length: 255)]
    private string $path;

    #[ORM\Column(type: 'integer')]
    private int $statusCode;

    public function __construct(
        string $service,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        string $method,
        string $path,
        int $statusCode
    ) {
        $this->uuid = Uuid::uuid4();
        $this->service = $service;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->method = $method;
        $this->path = $path;
        $this->statusCode = $statusCode;
    }

    public function getId(): ?int
    {
        return $this->id;
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

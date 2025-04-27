<?php

declare(strict_types=1);

namespace Domain\Log\ValueObject;

final readonly class LogFilters
{
    public function __construct(
        private ?array              $serviceNames = null,
        private ?int                $statusCode = null,
        private ?\DateTimeImmutable $startDate = null,
        private ?\DateTimeImmutable $endDate = null
    ) {
    }

    public function getServiceNames(): ?array
    {
        return $this->serviceNames;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function toArray(): array
    {
        return [
            'serviceNames' => $this->serviceNames,
            'statusCode' => $this->statusCode,
            'startDate' => $this->startDate?->format(\DateTimeInterface::ATOM),
            'endDate' => $this->endDate?->format(\DateTimeInterface::ATOM),
        ];
    }
}

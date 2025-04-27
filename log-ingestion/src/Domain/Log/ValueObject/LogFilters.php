<?php

declare(strict_types=1);

namespace Domain\Log\ValueObject;

final class LogFilters
{
    private ?array $serviceNames;
    private ?int $statusCode;
    private ?\DateTimeImmutable $startDate;
    private ?\DateTimeImmutable $endDate;

    public function __construct(
        ?array $serviceNames = null,
        ?int $statusCode = null,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null
    ) {
        $this->serviceNames = $serviceNames;
        $this->statusCode = $statusCode;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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

<?php

declare(strict_types=1);

namespace Domain\Log\ValueObject;

use App\Domain\Log\Enum\LogFilters as FiltersEnum;

final readonly class LogFilters
{
    public function __construct(
        private ?array $serviceNames = null,
        private ?int $statusCode = null,
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

    public function getActiveFilters(): array
    {
        return array_filter([
            FiltersEnum::SERVICE_NAMES->value => $this->serviceNames,
            FiltersEnum::STATUS_CODE->value => $this->statusCode,
            FiltersEnum::START_DATE->value => $this->startDate,
            FiltersEnum::END_DATE->value => $this->endDate,
        ], fn($value) => $value !== null && $value !== []);
    }

    public function toArray(): array
    {
        return [
            FiltersEnum::SERVICE_NAMES->value => $this->serviceNames,
            FiltersEnum::STATUS_CODE->value => $this->statusCode,
            FiltersEnum::START_DATE->value => $this->startDate?->format(\DateTimeInterface::ATOM),
            FiltersEnum::END_DATE->value => $this->endDate?->format(\DateTimeInterface::ATOM),
        ];
    }
}

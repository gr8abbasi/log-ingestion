<?php

declare(strict_types=1);

namespace Interface\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Domain\Log\ValueObject\LogFilters;

class LogRequest
{
    #[Assert\All([
        new Assert\Type('string'),
    ])]
    public ?array $serviceNames = [];

    #[Assert\Type('integer')]
    #[Assert\Range(min: 100, max: 599)]
    public ?int $statusCode = null;

    #[Assert\Type(\DateTimeInterface::class)]
    public ?\DateTimeImmutable $startDate = null;

    #[Assert\Type(\DateTimeInterface::class)]
    public ?\DateTimeImmutable $endDate = null;

    public function toFilters(): LogFilters
    {
        return new LogFilters(
            $this->serviceNames ?: null,
            $this->statusCode,
            $this->startDate,
            $this->endDate
        );
    }
}

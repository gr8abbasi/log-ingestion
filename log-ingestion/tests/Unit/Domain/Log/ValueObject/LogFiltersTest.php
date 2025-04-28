<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Log\ValueObject;

use Domain\Log\ValueObject\LogFilters;
use PHPUnit\Framework\TestCase;

class LogFiltersTest extends TestCase
{
    public function testItCreatesLogFiltersWithGivenValues()
    {
        $serviceNames = ['service1', 'service2'];
        $statusCode = 200;
        $startDate = new \DateTimeImmutable('2025-04-01 00:00:00');
        $endDate = new \DateTimeImmutable('2025-04-01 01:00:00');

        $logFilters = new LogFilters(
            $serviceNames,
            $statusCode,
            $startDate,
            $endDate
        );

        $this->assertSame($serviceNames, $logFilters->getServiceNames());
        $this->assertSame($statusCode, $logFilters->getStatusCode());
        $this->assertSame($startDate, $logFilters->getStartDate());
        $this->assertSame($endDate, $logFilters->getEndDate());
    }

    public function testItCreatesLogFiltersWithNullValues()
    {
        $logFilters = new LogFilters();

        $this->assertNull($logFilters->getServiceNames());
        $this->assertNull($logFilters->getStatusCode());
        $this->assertNull($logFilters->getStartDate());
        $this->assertNull($logFilters->getEndDate());
    }

    public function testItConvertsToArray()
    {
        $serviceNames = ['service1', 'service2'];
        $statusCode = 200;
        $startDate = new \DateTimeImmutable('2025-04-01 00:00:00');
        $endDate = new \DateTimeImmutable('2025-04-01 01:00:00');

        $logFilters = new LogFilters(
            $serviceNames,
            $statusCode,
            $startDate,
            $endDate
        );

        $expectedArray = [
            'serviceNames' => $serviceNames,
            'statusCode' => $statusCode,
            'startDate' => $startDate->format(\DateTimeInterface::ATOM),
            'endDate' => $endDate->format(\DateTimeInterface::ATOM),
        ];

        $this->assertSame($expectedArray, $logFilters->toArray());
    }
}

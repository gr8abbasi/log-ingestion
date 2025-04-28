<?php

declare(strict_types=1);

namespace Tests\Unit\Interface\Http\Request;

use Interface\Http\Request\LogRequest;
use Domain\Log\ValueObject\LogFilters;
use PHPUnit\Framework\TestCase;

class LogRequestTest extends TestCase
{
    public function testToFiltersReturnsCorrectLogFilters(): void
    {
        $request = new LogRequest();
        $request->serviceNames = ['auth', 'payment'];
        $request->statusCode = 200;
        $request->startDate = new \DateTimeImmutable('2024-01-01T00:00:00+00:00');
        $request->endDate = new \DateTimeImmutable('2024-12-31T23:59:59+00:00');

        $filters = $request->toFilters();

        $this->assertInstanceOf(LogFilters::class, $filters);
        $this->assertEquals(['auth', 'payment'], $filters->getServiceNames());
        $this->assertEquals(200, $filters->getStatusCode());
        $this->assertEquals($request->startDate, $filters->getStartDate());
        $this->assertEquals($request->endDate, $filters->getEndDate());
    }

    public function testToFiltersHandlesEmptyServiceNames(): void
    {
        $request = new LogRequest();
        $request->serviceNames = [];
        $request->statusCode = null;

        $filters = $request->toFilters();

        $this->assertNull($filters->getServiceNames());
        $this->assertNull($filters->getStatusCode());
    }

    public function testToFiltersHandlesNullDates(): void
    {
        $request = new LogRequest();
        $request->startDate = null;
        $request->endDate = null;

        $filters = $request->toFilters();

        $this->assertNull($filters->getStartDate());
        $this->assertNull($filters->getEndDate());
    }
}

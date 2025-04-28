<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Log\Entity;

use Domain\Log\Entity\LogEntry;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class LogEntryTest extends TestCase
{
    public function testItCreatesLogEntryWithExpectedValues(): void
    {
        $service = 'test-service';
        $startDate = new \DateTimeImmutable('2025-04-01 12:00:00');
        $endDate = new \DateTimeImmutable('2025-04-01 12:05:00');
        $method = 'POST';
        $path = '/api/test';
        $statusCode = 201;

        $logEntry = new LogEntry(
            $service,
            $startDate,
            $endDate,
            $method,
            $path,
            $statusCode
        );

        $this->assertInstanceOf(UuidInterface::class, $logEntry->getUuid());
        $this->assertEquals($service, $logEntry->getService());
        $this->assertEquals($startDate, $logEntry->getStartDate());
        $this->assertEquals($endDate, $logEntry->getEndDate());
        $this->assertEquals($method, $logEntry->getMethod());
        $this->assertEquals($path, $logEntry->getPath());
        $this->assertEquals($statusCode, $logEntry->getStatusCode());
    }
}

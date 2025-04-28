<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Log\ValueObject;

use Domain\Log\ValueObject\LogEntry;
use PHPUnit\Framework\TestCase;

class LogEntryTest extends TestCase
{
    public function testItCreatesLogEntryWithExpectedValues()
    {
        $service = 'test-service';
        $startDate = new \DateTimeImmutable('2025-04-01 00:00:00');
        $endDate = new \DateTimeImmutable('2025-04-01 01:00:00');
        $method = 'GET';
        $path = '/test-path';
        $statusCode = 200;

        $logEntry = new LogEntry(
            $service,
            $startDate,
            $endDate,
            $method,
            $path,
            $statusCode
        );

        $this->assertSame($service, $logEntry->service);
        $this->assertSame($startDate, $logEntry->startDate);
        $this->assertSame($endDate, $logEntry->endDate);
        $this->assertSame($method, $logEntry->method);
        $this->assertSame($path, $logEntry->path);
        $this->assertSame($statusCode, $logEntry->statusCode);
    }
}

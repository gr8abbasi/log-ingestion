<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Parser;

use Domain\Log\ValueObject\LogEntry;
use Infrastructure\Log\Offset\LogOffsetTracker;
use Infrastructure\Log\Parser\SimpleLogParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SimpleLogParserTest extends TestCase
{
    private string $logFilePath;
    private MockObject|LogOffsetTracker $offsetTracker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary file with fake log lines
        $this->logFilePath = tempnam(sys_get_temp_dir(), 'log');

        file_put_contents($this->logFilePath, implode(PHP_EOL, [
            'SERVICE-A [2025-04-01T12:00:00+00:00] "GET /api/test HTTP/1.1" 200',
            'SERVICE-B [2025-04-01T12:05:00+00:00] "POST /api/data HTTP/1.1" 500',
            '', // end of file marker
        ]));

        // Mock the offset tracker
        $this->offsetTracker = $this->createMock(LogOffsetTracker::class);
        $this->offsetTracker->method('getLastOffset')->willReturn(0);
    }

    protected function tearDown(): void
    {
        @unlink($this->logFilePath);
        parent::tearDown();
    }

    public function testParsesValidLogEntries(): void
    {
        $parser = new SimpleLogParser($this->offsetTracker);

        $entries = [];

        // Run the generator for a short time and break after expected entries
        foreach ($parser->parse($this->logFilePath) as $entry) {
            $entries[] = $entry;
            if (count($entries) === 2) {
                break;
            }
        }

        $this->assertCount(2, $entries);

        $this->assertInstanceOf(LogEntry::class, $entries[0]);
        $this->assertSame('SERVICE-A', $entries[0]->service);
        $this->assertSame('GET', $entries[0]->method);
        $this->assertSame('/api/test', $entries[0]->path);
        $this->assertSame(200, $entries[0]->statusCode);

        $this->assertSame('SERVICE-B', $entries[1]->service);
        $this->assertSame('POST', $entries[1]->method);
        $this->assertSame('/api/data', $entries[1]->path);
        $this->assertSame(500, $entries[1]->statusCode);
    }
}

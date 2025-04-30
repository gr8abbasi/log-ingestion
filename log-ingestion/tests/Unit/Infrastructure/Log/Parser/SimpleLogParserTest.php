<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Parser;

use Domain\Log\Entity\LogEntry;
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

        $this->logFilePath = tempnam(sys_get_temp_dir(), 'log');

        file_put_contents($this->logFilePath, implode(PHP_EOL, [
            'SERVICE-A [2025-04-01T12:00:00+00:00] "GET /api/test HTTP/1.1" 200',
            'SERVICE-B [2025-04-01T12:05:00+00:00] "POST /api/data HTTP/1.1" 500',
            '',
        ]));

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
        $this->assertSame('SERVICE-A', $entries[0]->getService());
        $this->assertSame('GET', $entries[0]->getMethod());
        $this->assertSame('/api/test', $entries[0]->getPath());
        $this->assertSame(200, $entries[0]->getStatusCode());

        $this->assertSame('SERVICE-B', $entries[1]->getService());
        $this->assertSame('POST', $entries[1]->getMethod());
        $this->assertSame('/api/data', $entries[1]->getPath());
        $this->assertSame(500, $entries[1]->getStatusCode());
    }
}

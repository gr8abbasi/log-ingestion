<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Tailer;

use Domain\Log\Entity\LogEntry;
use Infrastructure\Log\Parser\SimpleLogParser;
use Infrastructure\Log\Tailer\LogTailer;
use PHPUnit\Framework\TestCase;

class LogTailerTest extends TestCase
{
    public function testTailDelegatesToParserAndYieldsLogEntries(): void
    {
        $logFilePath = '/var/log/test.log';

        $logEntry1 = $this->createMock(LogEntry::class);
        $logEntry2 = $this->createMock(LogEntry::class);

        $mockParser = $this->createMock(SimpleLogParser::class);
        $mockParser->expects($this->once())
            ->method('parse')
            ->with($logFilePath)
            ->willReturn($this->createGenerator([$logEntry1, $logEntry2]));

        $tailer = new LogTailer($mockParser, $logFilePath);

        $result = iterator_to_array($tailer->tail());

        $this->assertCount(2, $result);
        $this->assertSame($logEntry1, $result[0]);
        $this->assertSame($logEntry2, $result[1]);
    }

    private function createGenerator(array $items): \Generator
    {
        foreach ($items as $item) {
            yield $item;
        }
    }
}

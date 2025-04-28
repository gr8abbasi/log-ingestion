<?php

declare(strict_types=1);

namespace Tests\Unit\Interface\Cli\Command;

use Interface\Cli\Command\IngestLogsCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Application\Log\Service\LogTailEventPublisherInterface;

class IngestLogsCommandTest extends TestCase
{
    public function testExecuteIngestsSuccessfully(): void
    {
        $tailLogService = $this->createMock(LogTailEventPublisherInterface::class);
        $tailLogService->method('execute')->willReturnCallback(function () {
        });

        $command = new IngestLogsCommand($tailLogService);
        $tester = new CommandTester($command);

        $status = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('Ingesting log entries...', $tester->getDisplay());
        $this->assertStringContainsString('Ingestion complete.', $tester->getDisplay());
    }

    public function testExecuteHandlesException(): void
    {
        $tailLogService = $this->createMock(LogTailEventPublisherInterface::class);
        $tailLogService->method('execute')->will($this->throwException(new \RuntimeException('Oops')));

        $command = new IngestLogsCommand($tailLogService);
        $tester = new CommandTester($command);

        $status = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('Failed to ingest logs: Oops', $tester->getDisplay());
    }
}

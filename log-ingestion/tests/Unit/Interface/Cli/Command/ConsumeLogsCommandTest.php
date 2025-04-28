<?php

declare(strict_types=1);

namespace Tests\Unit\Interface\Cli\Command;

use Interface\Cli\Command\ConsumeLogsCommand;
use Infrastructure\Log\Messaging\Kafka\KafkaMessageConsumer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;

class ConsumeLogsCommandTest extends TestCase
{
    public function testExecuteRunsConsumerSuccessfully(): void
    {
        $mockConsumer = $this->createMock(KafkaMessageConsumer::class);
        $mockConsumer->expects($this->once())->method('consume');

        $command = new ConsumeLogsCommand($mockConsumer);
        $tester = new CommandTester($command);

        $statusCode = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $statusCode);
        $this->assertStringContainsString('Starting Kafka consumer...', $tester->getDisplay());
    }

    public function testExecuteHandlesExceptionsGracefully(): void
    {
        $mockConsumer = $this->createMock(KafkaMessageConsumer::class);
        $mockConsumer->expects($this->once())
            ->method('consume')
            ->willThrowException(new \RuntimeException('Kafka down'));

        $command = new ConsumeLogsCommand($mockConsumer);
        $tester = new CommandTester($command);

        $statusCode = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $statusCode);
        $this->assertStringContainsString('Error: Something went wrong: Kafka down', $tester->getDisplay());
    }
}

<?php

declare(strict_types=1);

namespace Interface\Cli\Command;

use Application\Log\Service\LogTailEventPublisherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'log-ingestion:ingest-logs',
    description: 'Parses and ingests logs from the log file and fire event.'
)]
class IngestLogsCommand extends Command
{
    public function __construct(
        private readonly LogTailEventPublisherInterface $tailLogService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Ingesting log entries...</info>');
        
        try {
            $this->tailLogService->execute();
            $output->writeln('<info> Ingestion complete.</info>');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error> Failed to ingest logs: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}

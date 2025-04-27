<?php

declare(strict_types=1);

namespace Interface\Cli\Command;

use Infrastructure\Log\Messaging\Kafka\KafkaMessageConsumer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'log-ingestion:consume-kafka',
    description: 'Consumes Kafka messages and persists them in the database as batches.'
)]
class ConsumeLogsCommand extends Command
{
    public function __construct(
        private readonly KafkaMessageConsumer $consumer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting Kafka consumer...</info>');

        try {
            $this->consumer->consume();
        } catch (\Throwable $e) {
            $output->writeln('<error>Error: Something went wrong: ' . $e->getMessage().'</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

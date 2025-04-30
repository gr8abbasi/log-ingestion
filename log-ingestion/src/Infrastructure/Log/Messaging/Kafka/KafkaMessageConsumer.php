<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Kafka;

use Application\Log\DTO\LogEntryMessageDto;
use RdKafka\KafkaConsumer;
use Application\Log\Service\LogEntryPersister;
use Application\Log\DLQ\DLQStrategyInterface;

class KafkaMessageConsumer
{
    private bool $running = true;

    public function __construct(
        private KafkaConsumer        $consumer,
        private LogEntryPersister    $logService,
        private DLQStrategyInterface $dlqStrategy,
        string                       $topic
    )
    {
        $this->consumer->subscribe([$topic]);

        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, fn() => $this->running = false);
            pcntl_signal(SIGINT, fn() => $this->running = false);
        }
    }

    public function consume(): void
    {
        echo "Waiting for Kafka messages...\n"; //TODO: use proper logging

        while ($this->running) {
            $message = $this->consumer->consume(2000);

            if ($message === null || $message->err) {
                if ($message && $message->err) {
                    echo "[Kafka Error] {$message->errstr()}\n"; //TODO: use proper logging
                }
                continue;
            }

            try {
                $payload = json_decode($message->payload, true);
                $logEntryDto = LogEntryMessageDto::fromArray($payload);
                $this->logService->process($logEntryDto);
                $this->consumer->commitAsync($message);
                echo "Kafka consumed message successfully...\n"; //TODO: use proper logging
            } catch (\Throwable $e) {
                $this->dlqStrategy->handle($message, $e);
            }
        }

        $this->logService->flush();
        echo "Consumer stopped gracefully.\n"; //TODO: use proper logging
    }
}

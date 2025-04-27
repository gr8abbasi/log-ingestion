<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Kafka;

use RdKafka\KafkaConsumer;
use RdKafka\Message;
use Application\Log\Service\LogMessageService;
use Application\Log\DLQ\DLQStrategyInterface;

class KafkaMessageConsumer
{
    private KafkaConsumer $consumer;
    private LogMessageService $logService;
    private DLQStrategyInterface $dlqStrategy;
    private bool $running = true;

    public function __construct(
        KafkaConsumer        $consumer,
        LogMessageService    $logService,
        DLQStrategyInterface $dlqStrategy,
        string               $topic
    )
    {
        $this->consumer = $consumer;
        $this->logService = $logService;
        $this->dlqStrategy = $dlqStrategy;

        $this->consumer->subscribe([$topic]);

        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, fn() => $this->running = false);
            pcntl_signal(SIGINT, fn() => $this->running = false);
        }
    }

    public function consume(): void
    {
        echo "📡 Waiting for Kafka messages...\n";

        while ($this->running) {
            $message = $this->consumer->consume(2000);

            if ($message === null || $message->err) {
                if ($message && $message->err) {
                    echo "[Kafka Error] {$message->errstr()}\n";
                }
                continue;
            }

            try {
                $payload = json_decode($message->payload, true, 512, JSON_THROW_ON_ERROR);
                $this->logService->process($payload);
                $this->consumer->commitAsync($message);
            } catch (\Throwable $e) {
                $this->dlqStrategy->handle($message, $e);
            }
        }

        $this->logService->flush();
        echo "🛑 Consumer stopped gracefully.\n";
    }
}

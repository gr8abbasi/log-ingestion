<?php

declare(strict_types=1);

namespace Application\Log\DLQ;

use RdKafka\Message;

interface DLQStrategyInterface
{
public function handle(Message $message, \Throwable $exception): void;
}

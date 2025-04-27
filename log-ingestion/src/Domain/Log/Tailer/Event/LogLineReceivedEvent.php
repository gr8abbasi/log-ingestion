<?php

declare(strict_types=1);

namespace Domain\Log\Tailer\Event;

use Domain\Log\ValueObject\LogEntry;
use Symfony\Contracts\EventDispatcher\Event;

class LogLineReceivedEvent extends Event
{
    public function __construct(public readonly LogEntry $logEntry)
    {
    }
}
<?php

declare(strict_types=1);

namespace Application\Log\Service;

use Domain\Log\Tailer\LogTailerInterface;
use Domain\Log\Tailer\Event\LogLineReceivedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TailLogService
{
    public function __construct(
        readonly private LogTailerInterface $logTailer,
        readonly private EventDispatcherInterface $eventDispatcher
    ) {}

    public function execute(): void
    {
        foreach ($this->logTailer->tail() as $logEntry) {
            $this->eventDispatcher->dispatch(new LogLineReceivedEvent($logEntry));
        }
    }
}
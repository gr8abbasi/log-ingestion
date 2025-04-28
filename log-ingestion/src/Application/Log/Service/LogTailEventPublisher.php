<?php

declare(strict_types=1);

namespace Application\Log\Service;

use Domain\Log\Tailer\LogTailerInterface;
use Domain\Log\Tailer\Event\LogLineReceivedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class LogTailEventPublisher implements LogTailEventPublisherInterface
{
    public function __construct(
        private LogTailerInterface $logTailer,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function execute(): void
    {
        foreach ($this->logTailer->tail() as $logEntry) {
            $this->eventDispatcher->dispatch(new LogLineReceivedEvent($logEntry));
        }
    }
}
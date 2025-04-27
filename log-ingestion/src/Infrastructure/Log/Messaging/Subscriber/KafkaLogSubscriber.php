<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Subscriber;

use Domain\Log\Tailer\Event\LogLineReceivedEvent;
use Domain\Log\Messaging\MessagePublisherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class KafkaLogSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessagePublisherInterface $publisher)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogLineReceivedEvent::class => 'onLogEntryReceived',
        ];
    }

    public function onLogEntryReceived(LogLineReceivedEvent $event): void
    {
        $logEntry = $event->logEntry;

        $this->publisher->publish('log.alerts', [
            'service' => $logEntry->service,
            'timestamp' => $logEntry->startDate->format(DATE_ATOM),
            'method' => $logEntry->method,
            'path' => $logEntry->path,
            'statusCode' => $logEntry->statusCode,
        ]);
    }
}

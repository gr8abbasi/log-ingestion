<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Subscriber;

use Domain\Log\Enum\LogEntry;
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

        $this->publisher->publish('log.alerts', [ //TODO: Get topic name from configuration/constant
            LogEntry::SERVICE->value => $logEntry->service,
            LogEntry::START_DATE->value => $logEntry->startDate->format(DATE_ATOM),
            LogEntry::END_DATE->value => $logEntry->endDate->format(DATE_ATOM),
            LogEntry::METHOD->value => $logEntry->method,
            LogEntry::PATH->value => $logEntry->path,
            LogEntry::STATUS_CODE->value => $logEntry->statusCode,
        ]);
    }
}

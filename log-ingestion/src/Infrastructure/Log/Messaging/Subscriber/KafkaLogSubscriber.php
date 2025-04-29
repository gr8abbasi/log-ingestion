<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Subscriber;

use Application\Log\DTO\LogEntryMessageDto;
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
        $logDto = new LogEntryMessageDto(
            $logEntry->service,
            $logEntry->startDate,
            $logEntry->endDate,
            $logEntry->method,
            $logEntry->path,
            $logEntry->statusCode,
        );

        //TODO: Get topic name from configuration/constant
        $this->publisher->publish('log.alerts', $logDto);
    }
}

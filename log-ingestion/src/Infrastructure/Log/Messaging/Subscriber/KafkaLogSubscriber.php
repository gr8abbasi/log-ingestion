<?php

declare(strict_types=1);

namespace Infrastructure\Log\Messaging\Subscriber;

use Application\Log\DTO\LogEntryMessageDto;
use Domain\Log\Tailer\Event\LogLineReceivedEvent;
use Domain\Log\Messaging\MessagePublisherInterface;
use Infrastructure\Log\Exception\KafkaPublisherException;
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

    /**
     * @throws KafkaPublisherException
     */
    public function onLogEntryReceived(LogLineReceivedEvent $event): void
    {
        $logEntry = $event->logEntry;
        $logDto = new LogEntryMessageDto(
            $logEntry->getService(),
            $logEntry->getStartDate(),
            $logEntry->getEndDate(),
            $logEntry->getMethod(),
            $logEntry->getPath(),
            $logEntry->getStatusCode(),
        );

        //TODO: Get topic name from configuration/constant
        try {
            $this->publisher->publish('log.alerts', $logDto);
        } catch (\Throwable $e) {
            throw KafkaPublisherException::fromPublishingFailure($e->getMessage(), $e);
        }
    }
}

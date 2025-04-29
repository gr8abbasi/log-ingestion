<?php

declare(strict_types=1);

namespace Domain\Log\Messaging;

use Application\Log\DTO\LogEntryMessageDto;

interface MessagePublisherInterface
{
    public function publish(string $topic, LogEntryMessageDto $logDto): void;
}
<?php

declare(strict_types=1);

namespace Infrastructure\Log\Persistence\Doctrine\Mapper;

use Domain\Log\Entity\LogEntry;
use Infrastructure\Log\Persistence\Doctrine\Entity\LogEntryDoctrine;

class LogEntryMapper
{
    public static function toDoctrine(LogEntry $logEntry): LogEntryDoctrine
    {
        return new LogEntryDoctrine(
            $logEntry->getService(),
            $logEntry->getStartDate(),
            $logEntry->getEndDate(),
            $logEntry->getMethod(),
            $logEntry->getPath(),
            $logEntry->getStatusCode(),
        );
    }

    public static function toDomainEntity(LogEntryDoctrine $logEntry): LogEntry
    {
        return new LogEntry(
            $logEntry->getService(),
            $logEntry->getStartDate(),
            $logEntry->getEndDate(),
            $logEntry->getMethod(),
            $logEntry->getPath(),
            $logEntry->getStatusCode()
        );
    }
}
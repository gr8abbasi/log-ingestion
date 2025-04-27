<?php

namespace Domain\Log\ValueObject;

final readonly class LogEntry
{
    public function __construct(
        public string             $service,
        public \DateTimeImmutable $startDate,
        public \DateTimeImmutable $endDate,
        public string             $method,
        public string             $path,
        public int                $statusCode
    ){
    }
}
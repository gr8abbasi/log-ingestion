<?php

namespace Domain\Log\Enum;

enum LogEntry: string
{
    case SERVICE = 'service';
    case START_DATE = 'startDate';
    case END_DATE = 'endDate';
    case METHOD = 'method';
    case PATH = 'path';
    case STATUS_CODE = 'statusCode';
}
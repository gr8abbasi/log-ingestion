<?php

namespace App\Domain\Log\Enum;

enum LogFilters: string
{
    case SERVICE_NAMES = 'serviceNames';
    case STATUS_CODE = 'statusCode';
    case START_DATE = 'startDate';
    case END_DATE = 'endDate';
}
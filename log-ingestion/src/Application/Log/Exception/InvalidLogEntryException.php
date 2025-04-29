<?php

declare(strict_types=1);

namespace Application\Log\Exception;

use Exception;

class InvalidLogEntryException extends Exception
{
    public static function fromPersistError(string $message): self
    {
        return new self("Failed to persist log entry: " . $message);
    }

    public static function fromQueryError(string $message): self
    {
        return new self("Error during query execution: " . $message);
    }
}

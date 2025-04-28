<?php

declare(strict_types=1);

namespace Infrastructure\Log\Offset;

class LogOffsetTracker
{
    private string $offsetFile;
    private int $lastOffset = 0;

    public function __construct(string $logFilePath)
    {
        $this->offsetFile = $logFilePath . '.offset';

        if (file_exists($this->offsetFile)) {
            $this->lastOffset = (int)file_get_contents($this->offsetFile);
        }
    }

    public function getLastOffset(): int
    {
        return $this->lastOffset;
    }

    public function updateOffset(int $offset): void
    {
        $this->lastOffset = $offset;

        $lock = getenv('APP_ENV') === 'dev' ? 0 : LOCK_EX; //TODO: Use configuration file instead

        file_put_contents($this->offsetFile, (string)$offset, $lock);
    }
}

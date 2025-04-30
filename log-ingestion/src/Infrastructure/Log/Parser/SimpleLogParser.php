<?php

declare(strict_types=1);

namespace Infrastructure\Log\Parser;

use Domain\Log\Entity\LogEntry;
use Infrastructure\Log\Offset\LogOffsetTracker;

class SimpleLogParser implements LogParserInterface
{
    private ?\SplFileObject $handle = null;
    private ?int $lastInode = null;

    public function __construct(readonly private LogOffsetTracker $offsetTracker)
    {
    }

    public function parse(string $filePath): \Generator
    {
        while (true) {
            clearstatcache(true, $filePath);

            if (!file_exists($filePath)) {
                sleep(1);
                continue;
            }

            $currentInode = fileinode($filePath);

            if ($this->lastInode !== $currentInode || $this->handle === null) {
                // Log file was rotated or not opened yet
                $this->openFile($filePath);
                $this->lastInode = $currentInode;
            }

            while (!$this->handle->eof()) {
                $offsetBefore = $this->handle->ftell();
                $line = $this->handle->fgets();

                if (!$line || trim($line) === '') {
                    break;
                }

                if (preg_match('/^([A-Z\-]+).+\[(.+?)\] "(POST|GET|PUT|DELETE) (.+?) HTTP.+?" (\d{3})$/', trim($line), $matches)) {
                    yield new LogEntry(
                        service: $matches[1],
                        startDate: new \DateTimeImmutable($matches[2]),
                        endDate: new \DateTimeImmutable($matches[2]),
                        method: $matches[3],
                        path: $matches[4],
                        statusCode: (int)$matches[5]
                    );
                }

                $offsetAfter = $this->handle->ftell();
                $this->offsetTracker->updateOffset($offsetAfter);
            }

            sleep(1);
        }
    }

    private function openFile(string $filePath): void
    {
        $this->handle = new \SplFileObject($filePath, 'r');
        $this->handle->fseek($this->offsetTracker->getLastOffset());
    }
}

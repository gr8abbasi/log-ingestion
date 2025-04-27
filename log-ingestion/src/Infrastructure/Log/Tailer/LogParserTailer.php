<?php

namespace Infrastructure\Log\Tailer;

use Domain\Log\Tailer\LogTailerInterface;
use Domain\Log\Entity\LogEntry;
use Infrastructure\Log\Parser\SimpleLogParser;

readonly class LogParserTailer implements LogTailerInterface
{
    public function __construct(
        private SimpleLogParser $parser,
        private string          $logFilePath
    ) {
    }

    /**
     * @return \Generator<LogEntry>
     */
    public function tail(): \Generator
    {
        yield from $this->parser->parse($this->logFilePath);
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Log\Parser;

interface LogParserInterface
{
    public function parse(string $filePath): \Generator;
}

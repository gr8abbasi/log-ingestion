<?php

declare(strict_types=1);

namespace Application\Log\Service;

interface LogTailEventPublisherInterface
{
    public function execute(): void;
}

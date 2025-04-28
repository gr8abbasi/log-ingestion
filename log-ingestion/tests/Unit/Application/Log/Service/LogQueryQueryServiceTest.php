<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Log\Service;

use Application\Log\Service\LogQueryQueryService;
use Application\Log\UseCase\CountLogsInterface;
use Domain\Log\ValueObject\LogFilters;
use PHPUnit\Framework\TestCase;

class LogQueryQueryServiceTest extends TestCase
{
    public function testCountLogsDelegatesToUseCaseAndReturnsResult(): void
    {
        $filters = new LogFilters();
        $expectedCount = 42;

        $countLogsMock = $this->createMock(CountLogsInterface::class);
        $countLogsMock->expects($this->once())
            ->method('execute')
            ->with($filters)
            ->willReturn($expectedCount);

        $service = new LogQueryQueryService($countLogsMock);

        $result = $service->countLogs($filters);

        $this->assertSame($expectedCount, $result);
    }
}

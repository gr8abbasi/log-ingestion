<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Log\UseCase;

use Application\Log\UseCase\CountLogs;
use Domain\Log\Repository\LogEntryRepositoryInterface;
use Domain\Log\ValueObject\LogFilters;
use PHPUnit\Framework\TestCase;

class CountLogsTest extends TestCase
{
    public function testItCountsLogsUsingRepository(): void
    {
        $mockRepository = $this->createMock(LogEntryRepositoryInterface::class);
        $filters = new LogFilters(
            serviceNames: ['test-service'],
            statusCode: 200,
            startDate: new \DateTimeImmutable('-1 day'),
            endDate: new \DateTimeImmutable('now')
        );

        $mockRepository
            ->expects($this->once())
            ->method('countByFilters')
            ->with($filters)
            ->willReturn(42);

        $useCase = new CountLogs($mockRepository);
        $result = $useCase->execute($filters);

        $this->assertEquals(42, $result);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Persistence\Repository;

use Domain\Log\Entity\LogEntry;
use Domain\Log\ValueObject\LogFilters;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Infrastructure\Log\Persistence\Repository\LogEntryRepository;
use PHPUnit\Framework\TestCase;

class LogEntryRepositoryTest extends TestCase
{
    public function testSavePersistsLogEntry(): void
    {
        $logEntry = $this->createMock(LogEntry::class);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($logEntry);

        $repo = new LogEntryRepository($em);
        $repo->save($logEntry);
    }

    public function testFlushCallsEntityManagerFlush(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $repo = new LogEntryRepository($em);
        $repo->flush();
    }

    public function testCountByFiltersBuildsCorrectQuery(): void
    {
        $filters = new LogFilters(
            serviceNames: ['AUTH-SERVICE'],
            statusCode: 200,
            startDate: new \DateTimeImmutable('2025-04-01T00:00:00+00:00'),
            endDate: new \DateTimeImmutable('2025-04-30T23:59:59+00:00')
        );

        $mockQuery = $this->createMock(Query::class);
        $mockQuery->expects($this->once())->method('getSingleScalarResult')->willReturn(1);

        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['select', 'from', 'andWhere', 'setParameter', 'getQuery'])
            ->getMock();

        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($mockQuery);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('createQueryBuilder')->willReturn($qb);

        $repo = new LogEntryRepository($em);
        $count = $repo->countByFilters($filters);

        $this->assertEquals(1, $count);
    }
}

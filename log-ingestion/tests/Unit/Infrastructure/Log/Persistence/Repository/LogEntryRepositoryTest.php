<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Persistence\Repository;

use Application\Log\Exception\InvalidLogEntryException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\TransactionRequiredException;
use Domain\Log\Entity\LogEntry;
use Domain\Log\ValueObject\LogFilters;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Infrastructure\Log\Persistence\Doctrine\Entity\LogEntryDoctrine;
use Infrastructure\Log\Persistence\Repository\LogEntryRepository;
use PHPUnit\Framework\TestCase;

class LogEntryRepositoryTest extends TestCase
{
    public function testSavePersistsLogEntry(): void
    {
        $logEntry = new LogEntry(
            service: 'auth-service',
            startDate: new \DateTimeImmutable('2025-04-28T12:00:00+00:00'),
            endDate: new \DateTimeImmutable('2025-04-28T12:01:00+00:00'),
            method: 'POST',
            path: '/login',
            statusCode: 200
        );

        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($entity) use ($logEntry) {
                return $entity instanceof LogEntryDoctrine
                    && $entity->getService() === $logEntry->getService()
                    && $entity->getMethod() === $logEntry->getMethod()
                    && $entity->getPath() === $logEntry->getPath()
                    && $entity->getStatusCode() === $logEntry->getStatusCode();
            }));

        $repo = new LogEntryRepository($em);
        $repo->save($logEntry);
    }

    public function testSaveThrowsExceptionOnPersistError(): void
    {
        $this->expectException(InvalidLogEntryException::class);

        $logEntry = new LogEntry(
            service: 'auth-service',
            startDate: new \DateTimeImmutable('2025-04-28T12:00:00+00:00'),
            endDate: new \DateTimeImmutable('2025-04-28T12:01:00+00:00'),
            method: 'POST',
            path: '/login',
            statusCode: 200
        );

        $em = $this->createMock(EntityManagerInterface::class);
        // Simulate an error during the persist operation
        $em->expects($this->once())
            ->method('persist')
            ->willThrowException(new OptimisticLockException('Optimistic lock error', new ClassMetadata('Entity')));

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

    public function testFlushThrowsExceptionOnFlushError(): void
    {
        $this->expectException(InvalidLogEntryException::class);

        $em = $this->createMock(EntityManagerInterface::class);
        // Simulate an error during the flush operation
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException(new TransactionRequiredException('Transaction required error'));

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

    public function testCountByFiltersThrowsExceptionOnQueryError(): void
    {
        $this->expectException(InvalidLogEntryException::class);

        $filters = new LogFilters(
            serviceNames: ['AUTH-SERVICE'],
            statusCode: 200,
            startDate: new \DateTimeImmutable('2025-04-01T00:00:00+00:00'),
            endDate: new \DateTimeImmutable('2025-04-30T23:59:59+00:00')
        );

        $mockQuery = $this->createMock(Query::class);
        // Simulate a query exception
        $mockQuery->expects($this->once())
            ->method('getSingleScalarResult')
            ->willThrowException(new QueryException('Query error'));

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
        $repo->countByFilters($filters);
    }
}

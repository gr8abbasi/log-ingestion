<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Persistence\Doctrine\Mapper;

use Domain\Log\Entity\LogEntry as DomainLogEntry;
use Infrastructure\Log\Persistence\Doctrine\Entity\LogEntryDoctrine as DoctrineLogEntry;
use Infrastructure\Log\Persistence\Doctrine\Mapper\LogEntryMapper;
use PHPUnit\Framework\TestCase;

class LogEntryMapperTest extends TestCase
{
    public function testToDoctrine(): void
    {
        $service = 'api-gateway';
        $startDate = new \DateTimeImmutable('2024-04-01 12:00:00');
        $endDate = new \DateTimeImmutable('2024-04-01 12:01:00');
        $method = 'POST';
        $path = '/submit';
        $statusCode = 201;

        $domainLog = new DomainLogEntry($service, $startDate, $endDate, $method, $path, $statusCode);

        $doctrineLog = LogEntryMapper::toDoctrine($domainLog);

        $this->assertInstanceOf(DoctrineLogEntry::class, $doctrineLog);
        $this->assertSame($service, $doctrineLog->getService());
        $this->assertEquals($startDate, $doctrineLog->getStartDate());
        $this->assertEquals($endDate, $doctrineLog->getEndDate());
        $this->assertSame($method, $doctrineLog->getMethod());
        $this->assertSame($path, $doctrineLog->getPath());
        $this->assertSame($statusCode, $doctrineLog->getStatusCode());
    }

    public function testToDomain(): void
    {
        $service = 'api-gateway';
        $startDate = new \DateTimeImmutable('2024-04-01 12:00:00');
        $endDate = new \DateTimeImmutable('2024-04-01 12:01:00');
        $method = 'POST';
        $path = '/submit';
        $statusCode = 201;

        $doctrineLog = new DoctrineLogEntry($service, $startDate, $endDate, $method, $path, $statusCode);

        $domainLog = LogEntryMapper::toDomainEntity($doctrineLog);

        $this->assertInstanceOf(DomainLogEntry::class, $domainLog);
        $this->assertSame($service, $domainLog->getService());
        $this->assertEquals($startDate, $domainLog->getStartDate());
        $this->assertEquals($endDate, $domainLog->getEndDate());
        $this->assertSame($method, $domainLog->getMethod());
        $this->assertSame($path, $domainLog->getPath());
        $this->assertSame($statusCode, $domainLog->getStatusCode());
    }
}

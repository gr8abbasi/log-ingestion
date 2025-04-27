<?php

declare(strict_types=1);

namespace Infrastructure\Log\Persistence\Repository;

use Domain\Log\ValueObject\LogFilters;
use Domain\Log\Entity\LogEntry;
use Doctrine\ORM\EntityManagerInterface;
use Domain\Log\Repository\LogEntryRepositoryInterface;

class LogEntryRepository implements LogEntryRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function save(LogEntry $entry): void
    {
        $this->entityManager->persist($entry);
        $this->entityManager->flush();
    }

    public function countByFilters(LogFilters $filters): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('COUNT(l.id)')
            ->from(LogEntry::class, 'l');

        if ($filters->getServiceNames()) {
            $qb->andWhere('l.service IN (:services)')
                ->setParameter('services', $filters->getServiceNames());
        }

        if ($filters->getStatusCode()) {
            $qb->andWhere('l.statusCode = :statusCode')
                ->setParameter('statusCode', $filters->getStatusCode());
        }

        if ($filters->getStartDate()) {
            $qb->andWhere('l.startDate >= :startDate')
                ->setParameter('startDate', $filters->getStartDate());
        }

        if ($filters->getEndDate()) {
            $qb->andWhere('l.endDate <= :endDate')
                ->setParameter('endDate', $filters->getEndDate());
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}

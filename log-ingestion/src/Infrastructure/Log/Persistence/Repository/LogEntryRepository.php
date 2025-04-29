<?php

declare(strict_types=1);

namespace Infrastructure\Log\Persistence\Repository;

use Application\Log\Exception\InvalidLogEntryException;
use Doctrine\ORM\NonUniqueResultException;
use Domain\Log\Entity\LogEntry;
use Domain\Log\ValueObject\LogFilters;
use Domain\Log\Repository\LogEntryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Infrastructure\Log\Persistence\Doctrine\Mapper\LogEntryMapper;
use Infrastructure\Log\Persistence\Doctrine\Entity\LogEntryDoctrine;

readonly class LogEntryRepository implements LogEntryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    /**
     * @throws InvalidLogEntryException
     */
    public function save(LogEntry $logEntry): void
    {
        try {
            $this->entityManager->persist(LogEntryMapper::toDoctrine($logEntry));
        } catch (\Exception $e) {
            throw InvalidLogEntryException::fromPersistError($e->getMessage());
        }
    }

    /**
     * @throws InvalidLogEntryException
     */
    public function flush(): void
    {
        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw InvalidLogEntryException::fromPersistError($e->getMessage());
        }
    }

    /**
     * @throws InvalidLogEntryException
     */
    public function countByFilters(LogFilters $filters): int
    {
        try {

            $qb = $this->entityManager->createQueryBuilder();

            $qb->select('COUNT(l.id)')
                ->from(LogEntryDoctrine::class, 'l');

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

            return (int)$qb->getQuery()->getSingleScalarResult();

        } catch (NonUniqueResultException|\Exception $e) {
            throw InvalidLogEntryException::fromQueryError($e->getMessage());
        }
    }
}

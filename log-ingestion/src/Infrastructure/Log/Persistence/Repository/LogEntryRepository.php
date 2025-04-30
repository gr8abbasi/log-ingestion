<?php

declare(strict_types=1);

namespace Infrastructure\Log\Persistence\Repository;

use Application\Log\Exception\InvalidLogEntryException;
use Doctrine\ORM\NonUniqueResultException;
use Domain\Log\Entity\LogEntry;
use Domain\Log\ValueObject\LogFilters;
use App\Domain\Log\Enum\LogFilters as FiltersEnum;
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

            $filterExpressions = [
                FiltersEnum::SERVICE_NAMES->value => 'l.service IN (:'. FiltersEnum::SERVICE_NAMES->value .')',
                FiltersEnum::STATUS_CODE->value => 'l.statusCode = :'. FiltersEnum::STATUS_CODE->value,
                FiltersEnum::START_DATE->value => 'l.startDate >= :'. FiltersEnum::START_DATE->value,
                FiltersEnum::END_DATE->value => 'l.endDate <= :'. FiltersEnum::END_DATE->value,
            ];

            foreach ($filters->getActiveFilters() as $filter => $value) {
                if (isset($filterExpressions[$filter])) {
                    $qb->andWhere($filterExpressions[$filter])
                        ->setParameter($filter, $value);
                }
            }

            return (int)$qb->getQuery()->getSingleScalarResult();

        } catch (NonUniqueResultException | \Exception $e) {
            throw InvalidLogEntryException::fromQueryError($e->getMessage());
        }
    }
}

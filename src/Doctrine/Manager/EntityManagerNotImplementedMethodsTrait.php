<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Manager;

use DateTimeInterface;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Cache;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;

trait EntityManagerNotImplementedMethodsTrait
{
    public function getCache(): Cache|null
    {
        // TODO: Implement getCache() method.
    }

    public function createQuery(string $dql = ''): Query
    {
        // TODO: Implement createQuery() method.
    }

    public function createNativeQuery(string $sql, ResultSetMapping $rsm): NativeQuery
    {
        // TODO: Implement createNativeQuery() method.
    }

    public function getReference(string $entityName, mixed $id): object|null
    {
        // TODO: Implement getReference() method.
    }

    public function lock(object $entity, int|LockMode $lockMode, DateTimeInterface|int|null $lockVersion = null): void
    {
        // TODO: Implement lock() method.
    }

    public function newHydrator(int|string $hydrationMode): AbstractHydrator
    {
        // TODO: Implement newHydrator() method.
    }

    public function initializeObject(object $obj): void
    {
        // TODO: Implement initializeObject() method.
    }

    public function isUninitializedObject(mixed $value): bool
    {
        // TODO: Implement isUninitializedObject() method.
    }
}

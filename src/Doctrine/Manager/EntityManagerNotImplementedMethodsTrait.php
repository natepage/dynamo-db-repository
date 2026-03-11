<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Manager;

use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Cache;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\ORM\Query\ResultSetMapping;

trait EntityManagerNotImplementedMethodsTrait
{
    public function getCache(): Cache|null
    {
        // TODO: Implement getCache() method.
    }

    public function getExpressionBuilder(): Expr
    {
        // TODO: Implement getExpressionBuilder() method.
    }

    public function beginTransaction(): void
    {
        // TODO: Implement beginTransaction() method.
    }

    public function wrapInTransaction(callable $func): mixed
    {
        // TODO: Implement wrapInTransaction() method.
    }

    public function commit(): void
    {
        // TODO: Implement commit() method.
    }

    public function rollback(): void
    {
        // TODO: Implement rollback() method.
    }

    public function createQuery(string $dql = ''): Query
    {
        // TODO: Implement createQuery() method.
    }

    public function createNativeQuery(string $sql, ResultSetMapping $rsm): NativeQuery
    {
        // TODO: Implement createNativeQuery() method.
    }

    public function find(string $className, mixed $id, int|LockMode|null $lockMode = null, ?int $lockVersion = null): object|null
    {
        // TODO: Implement find() method.
    }

    public function refresh(object $object, int|LockMode|null $lockMode = null): void
    {
        // TODO: Implement refresh() method.
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

    public function getProxyFactory(): ProxyFactory
    {
        // TODO: Implement getProxyFactory() method.
    }

    public function getFilters(): FilterCollection
    {
        // TODO: Implement getFilters() method.
    }

    public function isFiltersStateClean(): bool
    {
        // TODO: Implement isFiltersStateClean() method.
    }

    public function hasFilters(): bool
    {
        // TODO: Implement hasFilters() method.
    }

    public function remove(object $object): void
    {
        // TODO: Implement remove() method.
    }

    public function clear(): void
    {
        // TODO: Implement clear() method.
    }

    public function detach(object $object): void
    {
        // TODO: Implement detach() method.
    }

    public function initializeObject(object $obj): void
    {
        // TODO: Implement initializeObject() method.
    }

    public function isUninitializedObject(mixed $value): bool
    {
        // TODO: Implement isUninitializedObject() method.
    }

    public function contains(object $object): bool
    {
        // TODO: Implement contains() method.
    }
}

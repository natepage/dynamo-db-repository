<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Persister;

use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Mapping\AssociationMapping;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Query\ResultSetMapping;

trait EntityPersisterNotImplementedMethodsTrait
{
    public function getResultSetMapping(): ResultSetMapping
    {
        // TODO: Implement getResultSetMapping() method.
    }

    public function getInsertSQL(): string
    {
        // TODO: Implement getInsertSQL() method.
    }

    public function getSelectSQL(array|Criteria $criteria, ?AssociationMapping $assoc = null, int|LockMode|null $lockMode = null, ?int $limit = null, ?int $offset = null, ?array $orderBy = null,): string
    {
        // TODO: Implement getSelectSQL() method.
    }

    public function getCountSQL(array|Criteria $criteria = []): string
    {
        // TODO: Implement getCountSQL() method.
    }

    public function expandParameters(array $criteria): array
    {
        // TODO: Implement expandParameters() method.
    }

    public function expandCriteriaParameters(Criteria $criteria): array
    {
        // TODO: Implement expandCriteriaParameters() method.
    }

    public function getSelectConditionStatementSQL(string $field, mixed $value, ?AssociationMapping $assoc = null, ?string $comparison = null,): string
    {
        // TODO: Implement getSelectConditionStatementSQL() method.
    }

    public function count(array|Criteria $criteria = []): int
    {
        // TODO: Implement count() method.
    }

    public function getOwningTable(string $fieldName): string
    {
        // TODO: Implement getOwningTable() method.
    }

    public function load(array $criteria, ?object $entity = null, ?AssociationMapping $assoc = null, array $hints = [], int|LockMode|null $lockMode = null, ?int $limit = null, ?array $orderBy = null,): object|null
    {
        // TODO: Implement load() method.
    }

    public function loadById(array $identifier, ?object $entity = null): object|null
    {
        // TODO: Implement loadById() method.
    }

    public function loadOneToOneEntity(AssociationMapping $assoc, object $sourceEntity, array $identifier = []): object|null
    {
        // TODO: Implement loadOneToOneEntity() method.
    }

    public function loadCriteria(Criteria $criteria): array
    {
        // TODO: Implement loadCriteria() method.
    }

    public function loadAll(array $criteria = [], ?array $orderBy = null, ?int $limit = null, ?int $offset = null,): array
    {
        // TODO: Implement loadAll() method.
    }

    public function getManyToManyCollection(AssociationMapping $assoc, object $sourceEntity, ?int $offset = null, ?int $limit = null,): array
    {
        // TODO: Implement getManyToManyCollection() method.
    }

    public function loadManyToManyCollection(AssociationMapping $assoc, object $sourceEntity, PersistentCollection $collection,): array
    {
        // TODO: Implement loadManyToManyCollection() method.
    }

    public function loadOneToManyCollection(AssociationMapping $assoc, object $sourceEntity, PersistentCollection $collection,): mixed
    {
        // TODO: Implement loadOneToManyCollection() method.
    }

    public function lock(array $criteria, int|LockMode $lockMode): void
    {
        // TODO: Implement lock() method.
    }

    public function getOneToManyCollection(AssociationMapping $assoc, object $sourceEntity, ?int $offset = null, ?int $limit = null,): array
    {
        // TODO: Implement getOneToManyCollection() method.
    }

    public function exists(object $entity, ?Criteria $extraConditions = null): bool
    {
        // TODO: Implement exists() method.
    }
}

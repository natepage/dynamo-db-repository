<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Persister;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister as EntityPersisterInterface;
use NatePage\DynamoDbRepository\Common\Repository\ObjectRepositoryInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class EntityPersister implements EntityPersisterInterface
{
    use EntityPersisterNotImplementedMethodsTrait;

    private array $inserts = [];

    public function __construct(
        private readonly ClassMetadata $classMetadata,
        private readonly ObjectRepositoryInterface $objectRepository,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function addInsert(object $entity): void
    {
        $this->inserts[\spl_object_id($entity)] = $entity;
    }

    public function delete(object $entity): bool
    {
        try {
            $this->objectRepository->delete($entity);

            return true;
        } catch (Throwable $throwable) {
            $this->logger?->error('Failed to delete entity', [
                'entity' => $entity::class,
                'exception' => $throwable->getMessage(),
            ]);

            return false;
        }
    }

    public function executeInserts(): void
    {
        foreach ($this->inserts as $insert) {
            $this->objectRepository->save($insert);
        }
    }

    public function getClassMetadata(): ClassMetadata
    {
        return $this->classMetadata;
    }

    public function getInserts(): array
    {
        return $this->inserts;
    }

    public function update(object $entity): void
    {
        $this->objectRepository->update($entity);
    }
}

<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\PropertyAccessors\PropertyAccessor;
use NatePage\DynamoDbRepository\Common\Repository\ObjectRepositoryInterface;

final class EntityRepository extends BaseEntityRepository
{
    public function __construct(
        private readonly ObjectRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClassMetadata $class
    ) {
        parent::__construct($entityManager, $class);
    }

    public function find(mixed $id, int|LockMode|null $lockMode = null, ?int $lockVersion = null): object|null
    {
        if (\is_string($id) === false) {
            return null;
        }

        $entity = $this->repository->find($id);

        if ($entity != null) {
            // Support repositories using an abstract entity class as their main object class,
            // we must resolve the class metadata for the concrete class to ensure it has all the propertyAccessors
            $classMetadata = $this->class;
            if ($classMetadata->rootEntityName !== $entity::class) {
                $concreteClasses = $this->repository::getObjectConcreteClasses();

                if (\is_array($concreteClasses) && \in_array($entity::class, $concreteClasses, true)) {
                    $classMetadata = $this->entityManager->getClassMetadata($entity::class);
                }
            }

            $id = [$classMetadata->identifier[0] => $id];

            // Very basic support of entity data, no support for associations
            $data = \array_map(
                static fn (PropertyAccessor $accessor): mixed => $accessor->getValue($entity),
                $classMetadata->propertyAccessors
            );

            $this->entityManager->getUnitOfWork()->registerManaged($entity, $id, $data);
        }

        return $entity;
    }
}

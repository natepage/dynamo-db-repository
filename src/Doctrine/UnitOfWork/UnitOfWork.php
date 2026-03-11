<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\UnitOfWork;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Persisters\Entity\EntityPersister as EntityPersisterInterface;
use Doctrine\ORM\UnitOfWork as BaseUnitOfWork;
use NatePage\DynamoDbRepository\Common\Registry\ObjectRepositoryRegistryInterface;
use NatePage\DynamoDbRepository\Doctrine\Persister\EntityPersister;
use Psr\Log\LoggerInterface;

final class UnitOfWork extends BaseUnitOfWork
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ObjectRepositoryRegistryInterface $objectRepositoryRegistry,
        private readonly ?LoggerInterface $logger = null,
    ) {
        parent::__construct($entityManager);
    }

    public function getEntityPersister(string $entityName): EntityPersisterInterface
    {
        return new EntityPersister(
            $this->entityManager->getClassMetadata($entityName),
            $this->objectRepositoryRegistry->get($entityName),
            $this->logger,
        );
    }
}

<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Manager;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository as DoctrineEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use NatePage\DynamoDbRepository\Common\Registry\ObjectRepositoryRegistryInterface;
use NatePage\DynamoDbRepository\Doctrine\Repository\EntityRepository;

final readonly class EntityManager implements EntityManagerInterface
{
    use EntityManagerNotImplementedMethodsTrait;

    public function __construct(
        private ObjectRepositoryRegistryInterface $objectRepositoryRegistry,
    ) {
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this);
    }

    public function getRepository(string $className): DoctrineEntityRepository
    {
        $repository = $this->objectRepositoryRegistry->get($className);

        return new EntityRepository($repository, $this, $this->getClassMetadata($className));
    }

    public function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    public function getClassMetadata(string $className): ClassMetadata
    {
        $repository = $this->objectRepositoryRegistry->get($className);

        $classMetadata = new ClassMetadata($className);
        $classMetadata->setIdentifier([$repository::getPrimaryKeyName()]);

        return $classMetadata;
    }
}

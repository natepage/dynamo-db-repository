<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Registry;

use Doctrine\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use NatePage\DynamoDbRepository\Common\Registry\ObjectRepositoryRegistryInterface;
use NatePage\DynamoDbRepository\Doctrine\Manager\EntityManager;
use NatePage\DynamoDbRepository\Doctrine\Repository\EntityRepository;

final class ManagerRegistry implements ManagerRegistryInterface
{
    use ManagerRegistryNotImplementedMethodsTrait;

    public function __construct(
        private readonly ObjectRepositoryRegistryInterface $objectRepositoryRegistry,
    ) {
    }

    public function getManager(?string $name = null): ObjectManager
    {
        return new EntityManager($this->objectRepositoryRegistry);
    }

    public function getManagerForClass(string $class): ObjectManager|null
    {
        return $this->objectRepositoryRegistry->has($class) ? $this->getManager() : null;
    }

    public function getRepository(string $persistentObject, ?string $persistentManagerName = null): ObjectRepository
    {
        /** @var EntityManager $manager */
        $manager = $this->getManagerForClass($persistentObject);

        return new EntityRepository(
            $this->objectRepositoryRegistry->get($persistentObject),
            $manager,
            $manager->getClassMetadata($persistentObject)
        );
    }
}

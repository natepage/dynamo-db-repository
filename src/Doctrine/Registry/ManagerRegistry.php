<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Registry;

use Doctrine\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use NatePage\DynamoDbRepository\Common\Registry\ObjectRepositoryRegistryInterface;
use NatePage\DynamoDbRepository\Doctrine\Manager\EntityManager;
use NatePage\DynamoDbRepository\Doctrine\Repository\EntityRepository;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\ResetInterface;

final class ManagerRegistry implements ManagerRegistryInterface, ResetInterface
{
    use ManagerRegistryNotImplementedMethodsTrait;

    private array $classToManagerName = [];

    private array $managers = [];

    public function __construct(
        private readonly ObjectRepositoryRegistryInterface $objectRepositoryRegistry,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?string $defaultManagerName = null,
        private readonly ?array $entityManagersServiceIds = null,
        private readonly ?ContainerInterface $ormConfigurations = null,
    ) {
    }

    public function getDefaultManagerName(): string
    {
        return $this->defaultManagerName ?? 'default';
    }

    public function getManager(?string $name = null): ObjectManager
    {
        $name ??= $this->getDefaultManagerName();

        if (isset($this->managers[$name])) {
            return $this->managers[$name];
        }

        $configuration = $this->ormConfigurations?->has($name) ? $this->ormConfigurations->get($name) : null;

        return $this->managers[$name] = new EntityManager($this->objectRepositoryRegistry, $configuration, $this->logger);
    }

    public function getManagerForClass(string $class): ObjectManager|null
    {
        if ($this->objectRepositoryRegistry->has($class) === false) {
            return null;
        }

        if (isset($this->classToManagerName[$class])) {
            return $this->getManager($this->classToManagerName[$class]);
        }

        foreach ($this->getManagerNames() as $managerName) {
            $manager = $this->getManager($managerName);

            if ($manager->getMetadataFactory()->isTransient($class) === false) {
                return $manager;
            }
        }

        return null;
    }

    public function getManagerNames(): array
    {
        return \array_keys($this->entityManagersServiceIds ?? []);
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

    public function reset(): void
    {
        $this->classToManagerName = [];
        $this->managers = [];
    }
}

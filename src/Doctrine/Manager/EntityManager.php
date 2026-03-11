<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Manager;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository as DoctrineEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\QueryBuilder;
use NatePage\DynamoDbRepository\Common\Registry\ObjectRepositoryRegistryInterface;
use NatePage\DynamoDbRepository\Doctrine\Repository\EntityRepository;
use Psr\Log\LoggerInterface;

final class EntityManager implements EntityManagerInterface
{
    use EntityManagerNotImplementedMethodsTrait;

    private ?ClassMetadataFactory $classMetadataFactory = null;

    private ?EventManager $eventManager = null;

    public function __construct(
        private readonly ObjectRepositoryRegistryInterface $objectRepositoryRegistry,
        private ?Configuration $configuration = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this);
    }

    public function getClassMetadata(string $className): ClassMetadata
    {
        try {
            return $this->getMetadataFactory()->getMetadataFor($className);
        } catch (\Throwable $throwable) {
             $this->logger?->error(\sprintf(
                 'Failed to get class metadata for "%s". Creating default one manually...', $className
             ), ['exception' => $throwable->getMessage()]);
        }

        $repository = $this->objectRepositoryRegistry->get($className);

        $classMetadata = new ClassMetadata($className);
        $classMetadata->setIdentifier([$repository::getPrimaryKeyName()]);

        return $classMetadata;
    }

    public function getConfiguration(): Configuration
    {
        if ($this->configuration !== null) {
            return $this->configuration;
        }

        $configuration = new Configuration();
        $configuration->setMetadataDriverImpl(new AttributeDriver([]));

        return $this->configuration = $configuration;
    }

    public function getEventManager(): EventManager
    {
        return $this->eventManager ??= new EventManager();
    }

    public function getMetadataFactory(): ClassMetadataFactory
    {
        if ($this->classMetadataFactory !== null) {
            return $this->classMetadataFactory;
        }

        $className = $this->getConfiguration()->getClassMetadataFactoryName();

        $factory = new $className();
        $factory->setEntityManager($this);

        $cache = $this->getConfiguration()->getMetadataCache();
        if ($cache !== null) {
            $factory->setCache($cache);
        }

        return $this->classMetadataFactory = $factory;
    }

    public function getRepository(string $className): DoctrineEntityRepository
    {
        $repository = $this->objectRepositoryRegistry->get($className);

        return new EntityRepository($repository, $this, $this->getClassMetadata($className));
    }

    public function isOpen(): bool
    {
        return true;
    }
}

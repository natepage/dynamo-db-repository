<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Common\Registry;

use NatePage\DynamoDbRepository\Common\Exception\RepositoryNotRegisteredException;
use NatePage\DynamoDbRepository\Common\Repository\ObjectRepositoryInterface;
use Psr\Container\ContainerInterface;

final readonly class ContainerObjectRepositoryRegistry implements ObjectRepositoryRegistryInterface
{
    public function __construct(
        private ContainerInterface $repositories,
    ) {
    }

    public function has(string $class): bool
    {
        return $this->repositories->has($class);
    }

    public function get(string $class): ObjectRepositoryInterface
    {
        if ($this->repositories->has($class)) {
            try {
                return $this->repositories->get($class);
            } catch (\Throwable $throwable) {
                throw new RepositoryNotRegisteredException(\sprintf(
                    'Error while retrieving repository for class "%s": %s', $class,
                    $throwable->getMessage()
                ), previous: $throwable);
            }
        }

        throw new RepositoryNotRegisteredException(\sprintf('No repository registered for class "%s".', $class));
    }
}

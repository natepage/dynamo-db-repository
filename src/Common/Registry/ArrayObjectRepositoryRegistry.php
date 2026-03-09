<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Common\Registry;

use NatePage\DynamoDbRepository\Common\Exception\RepositoryNotRegisteredException;
use NatePage\DynamoDbRepository\Common\Repository\ObjectRepositoryInterface;

final readonly class ArrayObjectRepositoryRegistry implements ObjectRepositoryRegistryInterface
{
    public function __construct(
        private array $repositories,
    ) {
    }

    public function get(string $class): ObjectRepositoryInterface
    {
        if (isset($this->repositories[$class])) {
            return $this->repositories[$class];
        }

        throw new RepositoryNotRegisteredException(\sprintf('No repository registered for class "%s".', $class));
    }
}

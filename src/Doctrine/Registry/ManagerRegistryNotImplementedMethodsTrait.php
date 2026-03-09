<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Registry;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

trait ManagerRegistryNotImplementedMethodsTrait
{
    public function getDefaultConnectionName(): string
    {
        // TODO: Implement getDefaultConnectionName() method.
    }

    public function getConnection(?string $name = null): object
    {
        // TODO: Implement getConnection() method.
    }

    public function getConnections(): array
    {
        // TODO: Implement getConnections() method.
    }

    public function getConnectionNames(): array
    {
        // TODO: Implement getConnectionNames() method.
    }

    public function getDefaultManagerName(): string
    {
        // TODO: Implement getDefaultManagerName() method.
    }

    public function getManagers(): array
    {
        // TODO: Implement getManagers() method.
    }

    public function resetManager(?string $name = null): ObjectManager
    {
        // TODO: Implement resetManager() method.
    }

    public function getManagerNames(): array
    {
        return [];
    }
}

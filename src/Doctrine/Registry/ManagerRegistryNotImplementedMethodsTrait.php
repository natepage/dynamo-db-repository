<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Registry;

use Doctrine\Persistence\ObjectManager;

trait ManagerRegistryNotImplementedMethodsTrait
{
    public function getConnection(?string $name = null): object
    {
        // TODO: Implement getConnection() method.
    }

    public function getConnections(): array
    {
        // TODO: Implement getConnections() method.
    }

    public function resetManager(?string $name = null): ObjectManager
    {
        // TODO: Implement resetManager() method.
    }
}

<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Common\Registry;

use NatePage\DynamoDbRepository\Common\Repository\ObjectRepositoryInterface;

interface ObjectRepositoryRegistryInterface
{
    /**
     * @phpstan-param class-string $class
     */
    public function get(string $class): ObjectRepositoryInterface;
}

<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Common\Naming;

final readonly class UnderscoreTableNamingStrategy implements TableNamingStrategyInterface
{
    public function classToTableName(string $className): string
    {
        if (\str_contains($className, '\\')) {
            $className = \substr($className, \strrpos($className, '\\') + 1);
        }

        return \strtolower(\preg_replace('/(?<=[a-z0-9])([A-Z])/', '_$1', $className));
    }
}

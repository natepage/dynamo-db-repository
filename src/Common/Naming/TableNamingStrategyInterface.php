<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Common\Naming;

interface TableNamingStrategyInterface
{
    public function classToTableName(string $className): string;
}

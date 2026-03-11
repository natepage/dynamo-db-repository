<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;

final class DynamoDbConnection extends Connection
{
    public function __construct(Driver $driver)
    {
        parent::__construct([], $driver);
    }

    public function beginTransaction(): void
    {
        // DynamoDB does not support transactions, so this method is a no-op.
    }

    public function commit(): void
    {
        // DynamoDB does not support transactions, so this method is a no-op.
    }

    public function isTransactionActive(): bool
    {
        return false;
    }

    public function rollBack(): void
    {
        // DynamoDB does not support transactions, so this method is a no-op.
    }
}

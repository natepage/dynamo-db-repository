<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Dbal;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\ServerVersionProvider;
use SensitiveParameter;

final readonly class DynamoDbDriver implements Driver
{
    public function connect(#[SensitiveParameter] array $params): DriverConnection
    {
        // TODO: Implement connect() method.
    }

    public function getDatabasePlatform(ServerVersionProvider $versionProvider): AbstractPlatform
    {
        return new MySQLPlatform();
    }

    public function getExceptionConverter(): ExceptionConverter
    {
        return new Driver\API\MySQL\ExceptionConverter();
    }
}

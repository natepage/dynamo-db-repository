<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Bundle\Enum;

enum ConfigTag: string
{
    case ObjectRepository = 'dynamo_db_repository.object_repository';
}

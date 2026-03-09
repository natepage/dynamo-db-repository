<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Bundle\Enum;

enum ConfigParam: string
{
    case TablePrefix = 'dynamo_db_repository.table_prefix';
}

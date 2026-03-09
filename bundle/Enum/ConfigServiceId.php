<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Bundle\Enum;

enum ConfigServiceId: string
{
    case DynamoDbClient = 'dynamo_db_repository.dynamo_db_client';

    case ItemObjectTransformer = 'dynamo_db_repository.item_object_transformer';
}

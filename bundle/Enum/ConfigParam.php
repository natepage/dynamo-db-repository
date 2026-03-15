<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Bundle\Enum;

enum ConfigParam: string
{
    case AutoMapperArrayAsJsonString = 'dynamo_db_repository.auto_mapper.array_as_json_string';

    case AutoMapperDatetimeClass = 'dynamo_db_repository.auto_mapper.datetime_class';

    case AutoMapperDatetimeFormat = 'dynamo_db_repository.auto_mapper.datetime_format';

    case AutoMapperDefaultStringIfNull = 'dynamo_db_repository.auto_mapper.default_string_if_null';

    case TablePrefix = 'dynamo_db_repository.table_prefix';
}

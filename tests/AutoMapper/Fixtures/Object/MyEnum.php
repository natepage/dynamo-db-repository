<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object;

enum MyEnum: string
{
    case First = 'first';
    case Second = 'second';
}

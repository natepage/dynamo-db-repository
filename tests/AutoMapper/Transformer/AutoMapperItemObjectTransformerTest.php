<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Tests\AutoMapper\Transformer;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use AutoMapper\AutoMapper;
use NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer\FromAttributeValuePropertyTransformer;
use NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer\ToAttributeValuePropertyTransformer;
use NatePage\DynamoDbRepository\AutoMapper\Transformer\AutoMapperItemObjectTransformer;
use NatePage\DynamoDbRepository\Tests\AbstractTestCase;
use NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\SimpleObject;

final class AutoMapperItemObjectTransformerTest extends AbstractTestCase
{
    public function testToItem(): void
    {
        $transformer = new AutoMapperItemObjectTransformer(AutoMapper::create(propertyTransformers: [
            new FromAttributeValuePropertyTransformer(),
            new ToAttributeValuePropertyTransformer(),
        ]));

        $item = $transformer->toItem(new SimpleObject(
            'id',
            'name',
            'description',
            ['simple', 'value']
        ));

        $object = $transformer->toObject(SimpleObject::class, $item);

        self::assertIsArray($item);
        self::assertCount(4, $item);
        self::assertInstanceOf(AttributeValue::class, $item['id'] ?? null);
        self::assertEquals('id', $item['id']->getS());
        self::assertInstanceOf(SimpleObject::class, $object);
        self::assertEquals('id', $object->id);
        self::assertEquals('name', $object->name);
        self::assertEquals('description', $object->description);
        self::assertEquals(['simple', 'value'], $object->tags);
    }
}

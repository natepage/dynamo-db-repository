<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Tests\AutoMapper\Transformer;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use NatePage\DynamoDbRepository\AutoMapper\Mapper\AutoMapperItemObjectMapper;
use NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\ItemDto;
use NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\MyEnum;
use NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\SimpleObject;
use NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\WithCollectionAddRemoveObject;
use NatePage\DynamoDbRepository\Tests\Fixture\Kernel\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * AutoMapper is instantiated differently in the context of the Symfony bundle, providing better control
 * over transformer factories order, allowing to ensure our property transformers run before the built-in ones.
 * This is why we run those tests within the context of Symfony.
 */
final class AutoMapperItemObjectTransformerTest extends KernelTestCase
{
    public function testToItem(): void
    {
        $transformer = self::getContainer()->get(AutoMapperItemObjectMapper::class);

        $now = new DateTimeImmutable();
        $simpleObject = new SimpleObject(
            id: 'my id',
            name: 'my name',
            description: null,
            tags: ['simple', 'value'],
            createdAt: $now,
            enum: MyEnum::First,
            count: 2,
            price: 1.99,
            enabled: false,
        );
        $simpleObject->addSubItem(new ItemDto('sub item 1'));

        $item = $transformer->toItem($simpleObject);

        self::assertIsArray($item);
        self::assertCount(10, $item);

        foreach ($item as $attribute) {
            self::assertInstanceOf(AttributeValue::class, $attribute);
        }

        self::assertEquals('my id', $item['id']->getS());
        self::assertEquals('my name', $item['name']->getS());
        self::assertEquals('__placeholder__', $item['description']->getS());
        self::assertEquals('["simple","value"]', $item['tags']->getS());
        self::assertEquals($now->format('Y-m-d\TH:i:s.vuP'), $item['createdAt']->getS());
        self::assertEquals('first', $item['enum']->getS());
        self::assertEquals('2', $item['count']->getN());
        self::assertEquals('1.99', $item['price']->getN());
        self::assertEquals('[{"name":"sub item 1"}]', $item['subItems']->getS());
        self::assertFalse($item['enabled']->getBool());

        $object = $transformer->toObject(SimpleObject::class, $item);

        self::assertEquals($simpleObject, $object);
    }

    public function testToItemWithCollection(): void
    {
        $transformer = self::getContainer()->get(AutoMapperItemObjectMapper::class);

        $items = [
            new ItemDto('item1'),
            new ItemDto('item2'),
        ];
        $object = new WithCollectionAddRemoveObject();
        $object->setItems(new ArrayCollection($items));

        $item = $transformer->toItem($object);
        $resultObject = $transformer->toObject(WithCollectionAddRemoveObject::class, $item);

        self::assertIsArray($item);
        self::assertCount(1, $item);
        self::assertInstanceOf(AttributeValue::class, $item['items'] ?? null);
        self::assertInstanceOf(WithCollectionAddRemoveObject::class, $resultObject);
        self::assertCount(2, $resultObject->getItems());
        self::assertEquals('item1', $resultObject->getItems()->get(0)->getName());
        self::assertEquals('item2', $resultObject->getItems()->get(1)->getName());
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel('test', true, $options['config'] ?? []);
    }
}

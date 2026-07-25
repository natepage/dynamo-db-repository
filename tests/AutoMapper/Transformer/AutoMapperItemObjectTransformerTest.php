<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Tests\AutoMapper\Transformer;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use Doctrine\Common\Collections\ArrayCollection;
use NatePage\DynamoDbRepository\AutoMapper\Transformer\AutoMapperItemObjectTransformer;
use NatePage\DynamoDbRepository\AutoMapper\ValueObject\Result;
use NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\ItemDto;
use NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\SimpleObject;
use NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\WithCollectionObject;
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
        $transformer = self::getContainer()->get(AutoMapperItemObjectTransformer::class);

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

    public function testToItemWithCollection(): void
    {
        $transformer = self::getContainer()->get(AutoMapperItemObjectTransformer::class);

        $object = new WithCollectionObject();
        $object->setItems(new ArrayCollection([
            new ItemDto('item1'),
            new ItemDto('item2'),
        ]));

        $item = $transformer->toItem($object);
        $resultObject = $transformer->toObject(WithCollectionObject::class, $item);

        self::assertIsArray($item);
        self::assertCount(1, $item);
        self::assertInstanceOf(AttributeValue::class, $item['items'] ?? null);
        self::assertInstanceOf(WithCollectionObject::class, $resultObject);
        self::assertCount(2, $resultObject->getItems());
        self::assertEquals('item1', $resultObject->getItems()->get(0)->getName());
        self::assertEquals('item2', $resultObject->getItems()->get(1)->getName());
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel('test', true, $options['config'] ?? []);
    }
}

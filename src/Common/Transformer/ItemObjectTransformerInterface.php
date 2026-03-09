<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Common\Transformer;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;

interface ItemObjectTransformerInterface
{
    /**
     * @return AttributeValue[]
     */
    public function toItem(object $entity, ?array $context = null): array;

    /**
     * @param AttributeValue[] $item
     */
    public function toObject(string $class, array $item, ?array $context = null): object;
}

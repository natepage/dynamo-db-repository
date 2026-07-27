<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\Mapper;

use AutoMapper\AutoMapperInterface;
use NatePage\DynamoDbRepository\AutoMapper\Transformer\AttributeValueTransformerFactory;
use NatePage\DynamoDbRepository\Common\Mapper\ItemObjectMapperInterface;

final readonly class AutoMapperItemObjectMapper implements ItemObjectMapperInterface
{
    public const string CONTEXT_KEY = 'natepage_dynamodb_repository';

    public function __construct(
        private AutoMapperInterface $autoMapper,
        private AttributeValueTransformerFactory $attributeValueTransformerFactory,
    ) {
    }

    public function toItem(object $entity, ?array $context = null): array
    {
        $context ??= [];
        $context[self::CONTEXT_KEY] = true;

        // Not super happy about that, but that's an easy way to ensure we can persist more than
        // one entity in the same request without having to worry about the state of the transformer factory.
        $this->attributeValueTransformerFactory->reset();

        return $this->autoMapper->map($entity, 'array', $context);
    }

    public function toObject(string $class, array $item, ?array $context = null): object
    {
        $context ??= [];
        $context[self::CONTEXT_KEY] = true;

        // Not super happy about that, but that's an easy way to ensure we can persist more than
        // one entity in the same request without having to worry about the state of the transformer factory.
        $this->attributeValueTransformerFactory->reset();

        return $this->autoMapper->map($item, $class, $context);
    }
}

<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\Transformer;

use AutoMapper\AutoMapperInterface;
use NatePage\DynamoDbRepository\Common\Transformer\ItemObjectTransformerInterface;

final readonly class AutoMapperItemObjectTransformer implements ItemObjectTransformerInterface
{
    public const string CONTEXT_KEY = 'natepage_dynamodb_repository';

    public function __construct(
        private AutoMapperInterface $autoMapper,
    ) {
    }

    public function toItem(object $entity, ?array $context = null): array
    {
        $context ??= [];
        $context[self::CONTEXT_KEY] = true;

        return $this->autoMapper->map($entity, 'array', $context);
    }

    public function toObject(string $class, array $item, ?array $context = null): object
    {
        $context ??= [];
        $context[self::CONTEXT_KEY] = true;

        return $this->autoMapper->map($item, $class, $context);
    }
}

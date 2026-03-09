<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use AutoMapper\Metadata\MapperMetadata;
use AutoMapper\Metadata\SourcePropertyMetadata;
use AutoMapper\Metadata\TargetPropertyMetadata;
use NatePage\DynamoDbRepository\AutoMapper\Transformer\AutoMapperItemObjectTransformer;
use Symfony\Component\TypeInfo\TypeIdentifier;

final readonly class ToAttributeValuePropertyTransformer extends AbstractAttributeValuePropertyTransformer
{
    public function compute(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): mixed {
        foreach (self::BUILT_IN_MAPPING as $type => $mapping) {
            if ($source->type?->isIdentifiedBy($type) ?? false) {
                return $mapping;
            }
        }

        if ($this->arrayAsJsonString && ($source->type?->isIdentifiedBy(TypeIdentifier::ARRAY) ?? false)) {
            return 'S';
        }

        return null;
    }

    public function transform(mixed $value, object|array $source, array $context, mixed $computed = null): mixed
    {
        if (isset($context[AutoMapperItemObjectTransformer::CONTEXT_KEY]) === false || $computed === null) {
            return $value;
        }

        // Support null values for all types, validation should be handled earlier in the process
        if ($value === null) {
            return AttributeValue::create(['NULL' => true]);
        }

        if (\is_array($value)) {
            $value = \json_encode($value);
        }

        return AttributeValue::create([$computed => $value]);
    }

    public function supports(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): bool {
        return $mapperMetadata->target === 'array';
    }
}

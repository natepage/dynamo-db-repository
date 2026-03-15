<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use AutoMapper\Metadata\MapperMetadata;
use AutoMapper\Metadata\SourcePropertyMetadata;
use AutoMapper\Metadata\TargetPropertyMetadata;
use BackedEnum;
use DateTimeInterface;
use NatePage\DynamoDbRepository\AutoMapper\Transformer\AutoMapperItemObjectTransformer;
use NatePage\Utils\Helper\StringHelper;
use Symfony\Component\TypeInfo\Type\BackedEnumType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\TypeIdentifier;

final class ToAttributeValuePropertyTransformer extends AbstractAttributeValuePropertyTransformer
{
    public function transform(mixed $value, object|array $source, array $context, mixed $computed = null): mixed
    {
        if (isset($context[AutoMapperItemObjectTransformer::CONTEXT_KEY]) === false || $computed === null) {
            return $value;
        }

        // Support null values for all types, validation should be handled earlier in the process
        if ($value === null) {
            // Allow to specify a default string for null values
            // This is useful to find items with null values using a filter expression,
            // as DynamoDB does not support filtering on null values
            if ($computed === self::MAPPING_STRING && StringHelper::isNotEmpty($this->defaultStringIfNull)) {
                return AttributeValue::create([$computed => $this->defaultStringIfNull]);
            }

            return AttributeValue::create(['NULL' => true]);
        }

        $value = match (true) {
            \is_array($value) => \json_encode($value),
            $value instanceof BackedEnum => $value->value,
            $value instanceof DateTimeInterface => $value->format($this->dateTimeFormat),
            default => $value,
        };

        return AttributeValue::create([$computed => $value]);
    }

    public function supports(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): bool {
        return $mapperMetadata->target === 'array';
    }

    protected function doCompute(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): ?string {
        $sourceType = $this->resolveWrappedType($source->type);

        if ($sourceType instanceof BackedEnumType) {
            $sourceType = $this->resolveWrappedType($sourceType->getBackingType());
        }

        if ($this->arrayAsJsonString && ($sourceType?->isIdentifiedBy(TypeIdentifier::ARRAY) ?? false)) {
            return self::MAPPING_STRING;
        }

        if ($sourceType instanceof ObjectType
            && \is_a($sourceType->getClassName(), DateTimeInterface::class, true)) {
            return self::MAPPING_STRING;
        }

        return $this->resolveBuiltInMapping($sourceType);
    }
}

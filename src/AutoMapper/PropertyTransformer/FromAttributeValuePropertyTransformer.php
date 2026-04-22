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

final class FromAttributeValuePropertyTransformer extends AbstractAttributeValuePropertyTransformer
{
    private const string ARRAY_AS_JSON_STRING_COMPUTED = 'array_as_json_string';

    private const string BACKED_ENUM_PREFIX_COMPUTED = 'backed_enum,';

    private const string DATETIME_PREFIX_COMPUTED = 'datetime,';

    public function transform(mixed $value, object|array $source, array $context, mixed $computed = null): mixed
    {
        if (isset($context[AutoMapperItemObjectTransformer::CONTEXT_KEY]) === false
            || $computed === null
            || ($value instanceof AttributeValue === false)) {
            return $value;
        }

        $attributeValueBody = $value->requestBody();
        $attributeValueString = $attributeValueBody[self::MAPPING_STRING] ?? null;

        if ($value->getNull() === true
            || (StringHelper::isNotEmpty($this->defaultStringIfNull) && $attributeValueString === $this->defaultStringIfNull)) {
            return null;
        }

        // Array as JSON string
        if ($computed === self::ARRAY_AS_JSON_STRING_COMPUTED
            && StringHelper::isNotEmpty($attributeValueString)
            && \json_validate($attributeValueString)) {
            return \json_decode($attributeValueString, true);
        }

        // BackedEnum
        if (\str_starts_with($computed, self::BACKED_ENUM_PREFIX_COMPUTED)) {
            $computed = \substr($computed, \strlen(self::BACKED_ENUM_PREFIX_COMPUTED));

            // enumClassName,mapping
            [$enumClass, $mapping] = \explode(',', $computed);

            /** @var class-string<BackedEnum> $enumClass */
            return $enumClass::tryFrom($attributeValueBody[$mapping] ?? null);
        }

        // Datetime
        if (\str_starts_with($computed, self::DATETIME_PREFIX_COMPUTED)) {
            $datetimeClass = \substr($computed, \strlen(self::DATETIME_PREFIX_COMPUTED));

            // If the target type is the interface itself, use a concrete class instead (e.g. DateTimeImmutable)
            if ($datetimeClass === DateTimeInterface::class) {
                $datetimeClass = $this->dateTimeClass;
            }

            /** @var class-string<DateTimeInterface> $datetimeClass */
            return $datetimeClass::createFromFormat($this->dateTimeFormat, $attributeValueString);
        }

        return $attributeValueBody[$computed] ?? null;
    }

    public function supports(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): bool {
        return $mapperMetadata->source === 'array';
    }

    protected function doCompute(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): ?string {
        $targetType = $this->resolveWrappedType($target->type);

        if ($this->arrayAsJsonString && ($targetType?->isIdentifiedBy(TypeIdentifier::ARRAY) ?? false)) {
            return self::ARRAY_AS_JSON_STRING_COMPUTED;
        }

        if ($targetType instanceof BackedEnumType) {
            $mapping = $this->resolveBuiltInMapping($this->resolveWrappedType($targetType->getBackingType()));

            if (StringHelper::isNotEmpty($mapping)) {
                return self::BACKED_ENUM_PREFIX_COMPUTED . $targetType->getClassName() . ',' . $mapping;
            }
        }

        if ($targetType instanceof ObjectType
            && \is_a($targetType->getClassName(), DateTimeInterface::class, true)) {
            return self::DATETIME_PREFIX_COMPUTED . $targetType->getClassName();
        }

        return $this->resolveBuiltInMapping($targetType);
    }
}

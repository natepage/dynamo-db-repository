<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use AutoMapper\Metadata\MapperMetadata;
use AutoMapper\Metadata\SourcePropertyMetadata;
use AutoMapper\Metadata\TargetPropertyMetadata;
use BackedEnum;
use NatePage\DynamoDbRepository\AutoMapper\Transformer\AutoMapperItemObjectTransformer;
use NatePage\Utils\Helper\StringHelper;
use Symfony\Component\TypeInfo\Type\BackedEnumType;
use Symfony\Component\TypeInfo\TypeIdentifier;

final readonly class FromAttributeValuePropertyTransformer extends AbstractAttributeValuePropertyTransformer
{
    private const string ARRAY_AS_JSON_STRING_COMPUTED = 'array_as_json_string';

    private const string BACKED_ENUM_PREFIX_COMPUTED = 'backed_enum,';

    public function compute(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): mixed {
        $targetType = $target->type instanceof BackedEnumType ? $target->type->getBackingType() : $target->type;

        foreach (self::BUILT_IN_MAPPING as $type => $mapping) {
            if ($targetType?->isIdentifiedBy($type) ?? false) {
                if ($target->type instanceof BackedEnumType) {
                    return \sprintf(
                        '%s%s,%s',
                        self::BACKED_ENUM_PREFIX_COMPUTED,
                        $target->type->getClassName(),
                        $mapping
                    );
                }

                return $mapping;
            }
        }

        if ($this->arrayAsJsonString && ($target->type?->isIdentifiedBy(TypeIdentifier::ARRAY) ?? false)) {
            return self::ARRAY_AS_JSON_STRING_COMPUTED;
        }

        return null;
    }

    public function transform(mixed $value, object|array $source, array $context, mixed $computed = null): mixed
    {
        if (isset($context[AutoMapperItemObjectTransformer::CONTEXT_KEY]) === false
            || $computed === null
            || ($value instanceof AttributeValue === false)) {
            return $value;
        }

        if ($value->getNull() === true) {
            return null;
        }

        if ($computed === self::ARRAY_AS_JSON_STRING_COMPUTED
            && StringHelper::isNotEmpty($value->getS())
            && \json_validate($value->getS())) {
            return \json_decode($value->getS(), true);
        }

        $attributeValueBody = $value->requestBody();

        if (\str_starts_with($computed, self::BACKED_ENUM_PREFIX_COMPUTED)) {
            $computed = \substr($computed, \strlen(self::BACKED_ENUM_PREFIX_COMPUTED));

            // enumClassName,mapping
            [$enumClass, $mapping] = \explode(',', $computed);

            /** @var BackedEnum $enumClass */
            return $enumClass::tryFrom($attributeValueBody[$mapping] ?? null);
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
}

<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use AutoMapper\Metadata\MapperMetadata;
use AutoMapper\Metadata\SourcePropertyMetadata;
use AutoMapper\Metadata\TargetPropertyMetadata;
use NatePage\DynamoDbRepository\AutoMapper\Transformer\AutoMapperItemObjectTransformer;
use NatePage\Utils\Helper\StringHelper;
use Symfony\Component\TypeInfo\TypeIdentifier;

final readonly class FromAttributeValuePropertyTransformer extends AbstractAttributeValuePropertyTransformer
{
    private const string ARRAY_AS_JSON_STRING_COMPUTED = 'array_as_json_string';

    public function compute(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): mixed {
        foreach (self::BUILT_IN_MAPPING as $type => $mapping) {
            if ($target->type?->isIdentifiedBy($type) ?? false) {
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

        return $value->requestBody()[$computed] ?? null;
    }

    public function supports(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): bool {
        return $mapperMetadata->source === 'array';
    }
}

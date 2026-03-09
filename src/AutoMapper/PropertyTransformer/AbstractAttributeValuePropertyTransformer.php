<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer;

use AutoMapper\Metadata\MapperMetadata;
use AutoMapper\Metadata\SourcePropertyMetadata;
use AutoMapper\Metadata\TargetPropertyMetadata;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerComputeInterface;
use NatePage\Utils\Helper\StringHelper;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\WrappingTypeInterface;
use Symfony\Component\TypeInfo\TypeIdentifier;

abstract class AbstractAttributeValuePropertyTransformer implements PropertyTransformerComputeInterface
{
    protected const array BUILT_IN_MAPPING = [
        TypeIdentifier::BOOL->value => 'BOOL',
        TypeIdentifier::INT->value => 'N',
        TypeIdentifier::FLOAT->value => 'N',
        TypeIdentifier::STRING->value => 'S',
    ];

    protected const string MAPPING_STRING = 'S';

    public function __construct(
        protected bool $arrayAsJsonString = true,
        protected string $dateTimeFormat = 'Y:m:d H:i:s',
    ) {
    }

    public function compute(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): mixed {
        if (StringHelper::isNotEmpty($mapperMetadata->dateTimeFormat)) {
            $this->dateTimeFormat = $mapperMetadata->dateTimeFormat;
        }

        return $this->doCompute($source, $target, $mapperMetadata);
    }

    abstract protected function doCompute(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): mixed;

    protected function resolveBuiltInMapping(?Type $type): ?string
    {
        foreach (self::BUILT_IN_MAPPING as $typeIdentifier => $mapping) {
            if ($type?->isIdentifiedBy($typeIdentifier) ?? false) {
                return $mapping;
            }
        }

        return null;
    }

    protected function resolveWrappedType(?Type $type): ?Type
    {
        while ($type instanceof WrappingTypeInterface) {
            $type = $type->getWrappedType();
        }

        return $type;
    }
}

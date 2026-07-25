<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer;

use AutoMapper\Metadata\MapperMetadata;
use AutoMapper\Metadata\SourcePropertyMetadata;
use AutoMapper\Metadata\TargetPropertyMetadata;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerComputeInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use NatePage\Utils\Helper\StringHelper;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\Type\WrappingTypeInterface;
use Symfony\Component\TypeInfo\TypeIdentifier;

abstract class AbstractAttributeValuePropertyTransformer implements PropertyTransformerComputeInterface
{
    protected const array BUILT_IN_MAPPING = [
        TypeIdentifier::BOOL->value => 'BOOL',
        TypeIdentifier::INT->value => self::MAPPING_NUMBER,
        TypeIdentifier::FLOAT->value => self::MAPPING_NUMBER,
        TypeIdentifier::STRING->value => self::MAPPING_STRING,
    ];

    protected const string MAPPING_NUMBER = 'N';

    protected const string MAPPING_STRING = 'S';

    public function __construct(
        protected bool $arrayAsJsonString = true,
        protected string $dateTimeClass = DateTimeImmutable::class,
        protected string $dateTimeFormat = DateTimeInterface::ATOM,
        protected bool $doctrineCollectionAsJsonString = true,
        protected ?string $defaultStringIfNull = null
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

    protected function isDoctrineCollection(?Type $type): bool
    {
        if (\interface_exists(Collection::class) === false) {
            return false;
        }

        return $type->isSatisfiedBy(static function (Type $assessType): bool {
            if ($assessType instanceof ObjectType === false) {
                return false;
            }

            $className = $assessType->getClassName();
            $implementedClasses = \class_implements($className);

            return $className === Collection::class
                || ($implementedClasses && \in_array(Collection::class, $implementedClasses, true));
        });
    }

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

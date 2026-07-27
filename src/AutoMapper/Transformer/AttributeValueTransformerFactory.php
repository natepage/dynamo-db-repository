<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\Transformer;

use AutoMapper\Metadata\MapperMetadata;
use AutoMapper\Metadata\SourcePropertyMetadata;
use AutoMapper\Metadata\TargetPropertyMetadata;
use AutoMapper\Transformer\AbstractArrayTransformer;
use AutoMapper\Transformer\ChainTransformerFactoryAwareInterface;
use AutoMapper\Transformer\ChainTransformerFactoryAwareTrait;
use AutoMapper\Transformer\NullableTransformer;
use AutoMapper\Transformer\PrioritizedTransformerFactoryInterface;
use AutoMapper\Transformer\TransformerFactoryInterface;
use AutoMapper\Transformer\TransformerInterface;

final class AttributeValueTransformerFactory
    implements TransformerFactoryInterface, PrioritizedTransformerFactoryInterface, ChainTransformerFactoryAwareInterface
{
    use ChainTransformerFactoryAwareTrait;

    private ?string $currentProperty = null;

    private ?string $originalSourceClass = null;

    public function __construct(
        protected bool $arrayAsJsonString = true,
        protected ?string $defaultStringIfNull = null
    ) {
    }

    public function getTransformer(
        SourcePropertyMetadata $source,
        TargetPropertyMetadata $target,
        MapperMetadata $mapperMetadata
    ): ?TransformerInterface {
        // Prevent infinite loop as getting subTransformer will call this method again
        // because we use exactly the same types
        if ($this->currentProperty === $source->property) {
            return null;
        }
        $this->currentProperty = $source->property;

        // Prevent transforming sub item objects to AttributeValue
        // This allows to transform array/Collection of objects to a single JSON string
        if ($this->originalSourceClass !== null
            && $mapperMetadata->source !== 'array'
            && $mapperMetadata->source !== $this->originalSourceClass) {
            return null;
        }
        $this->originalSourceClass = $mapperMetadata->source !== 'array' ? $mapperMetadata->source : null;

        $subItemTransformer = $this->chainTransformerFactory->getTransformer($source, $target, $mapperMetadata);
        if ($subItemTransformer === null) {
            return null;
        }

        $isArrayProperty = false;
        if ($subItemTransformer instanceof NullableTransformer) {
            $actualTransformerClass = \str_replace(
                ['AutoMapper\Transformer\NullableTransformer', '<', '>'],
                '',
                (string)$subItemTransformer
            );
            $isArrayProperty = \is_a($actualTransformerClass, AbstractArrayTransformer::class, true);
        }

        $transformerClass = $mapperMetadata->target === 'array'
            ? ToAttributeValueTransformer::class
            : FromAttributeValueTransformer::class;

        return new $transformerClass(
            valueTransformer: $subItemTransformer,
            arrayAsJsonString: $this->arrayAsJsonString,
            defaultStringIfNull: $this->defaultStringIfNull,
            isArrayProperty: $isArrayProperty,
        );
    }

    public function getPriority(): int
    {
        return 1000;
    }
}

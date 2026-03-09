<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer;

use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerComputeInterface;
use Symfony\Component\TypeInfo\TypeIdentifier;

abstract readonly class AbstractAttributeValuePropertyTransformer implements PropertyTransformerComputeInterface
{
    protected const array BUILT_IN_MAPPING = [
        TypeIdentifier::BOOL->value => 'BOOL',
        TypeIdentifier::INT->value => 'N',
        TypeIdentifier::FLOAT->value => 'N',
        TypeIdentifier::STRING->value => 'S',
    ];

    public function __construct(
        protected bool $arrayAsJsonString = true
    ) {
    }
}

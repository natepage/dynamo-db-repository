<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer\FromAttributeValuePropertyTransformer;
use NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer\ToAttributeValuePropertyTransformer;
use NatePage\DynamoDbRepository\AutoMapper\Transformer\AutoMapperItemObjectTransformer;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigServiceId;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Property transformers
    $services
        ->set(FromAttributeValuePropertyTransformer::class)
        ->set(ToAttributeValuePropertyTransformer::class);

    // ItemObject transformer
    $services->set(ConfigServiceId::ItemObjectTransformer->value, AutoMapperItemObjectTransformer::class);
};

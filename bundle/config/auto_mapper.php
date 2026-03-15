<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer\FromAttributeValuePropertyTransformer;
use NatePage\DynamoDbRepository\AutoMapper\PropertyTransformer\ToAttributeValuePropertyTransformer;
use NatePage\DynamoDbRepository\AutoMapper\Transformer\AutoMapperItemObjectTransformer;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigParam;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigServiceId;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('bool $arrayAsJsonString', param(ConfigParam::AutoMapperArrayAsJsonString->value))
        ->bind('string $dateTimeClass', param(ConfigParam::AutoMapperDatetimeClass->value))
        ->bind('string $dateTimeFormat', param(ConfigParam::AutoMapperDatetimeFormat->value))
        ->bind('string $defaultStringIfNull', param(ConfigParam::AutoMapperDefaultStringIfNull->value));

    // Property transformers
    $services
        ->set(FromAttributeValuePropertyTransformer::class)
        ->set(ToAttributeValuePropertyTransformer::class);

    // ItemObject transformer
    $services->set(ConfigServiceId::ItemObjectTransformer->value, AutoMapperItemObjectTransformer::class);
};

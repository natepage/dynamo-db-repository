<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use NatePage\DynamoDbRepository\AutoMapper\Mapper\AutoMapperItemObjectMapper;
use NatePage\DynamoDbRepository\AutoMapper\Transformer\AttributeValueTransformerFactory;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigParam;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigServiceId;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('bool $arrayAsJsonString', param(ConfigParam::AutoMapperArrayAsJsonString->value))
        ->bind('string $defaultStringIfNull', param(ConfigParam::AutoMapperDefaultStringIfNull->value));

    $services
        ->set(AttributeValueTransformerFactory::class)
        ->tag('automapper.transformer_factory', ['priority' => 2003]);

    // ItemObject transformer
    $services->set(ConfigServiceId::ItemObjectTransformer->value, AutoMapperItemObjectMapper::class);
};

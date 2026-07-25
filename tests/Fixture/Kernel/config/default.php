<?php
declare(strict_types=1);

use NatePage\DynamoDbRepository\AutoMapper\Transformer\AutoMapperItemObjectTransformer;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigParam;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('framework', ['test' => true]);

    $container->extension('automapper', [
        'loader' => [
            'cache_dir' => '%kernel.cache_dir%/automapper',
            'reload_strategy' => 'always',
        ],
    ]);

    $container->extension('dynamo_db_repository', [
        'auto_mapper' => [
            'enabled' => true,
            'array_as_json_string' => true,
            'doctrine_collection_as_json_string' => true,
        ],
    ]);

    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(AutoMapperItemObjectTransformer::class)
        ->public();
};

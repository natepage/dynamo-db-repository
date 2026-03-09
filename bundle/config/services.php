<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AsyncAws\DynamoDb\DynamoDbClient;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigServiceId;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigTag;
use NatePage\DynamoDbRepository\Common\Naming\TableNamingStrategyInterface;
use NatePage\DynamoDbRepository\Common\Naming\UnderscoreTableNamingStrategy;
use NatePage\DynamoDbRepository\Common\Registry\ContainerObjectRepositoryRegistry;
use NatePage\DynamoDbRepository\Common\Registry\ObjectRepositoryRegistryInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Client
    $services->set(ConfigServiceId::DynamoDbClient->value, DynamoDbClient::class);

    // Registry
    $services
        ->set(ObjectRepositoryRegistryInterface::class, ContainerObjectRepositoryRegistry::class)
        ->arg('$repositories', tagged_locator(
            tag: ConfigTag::ObjectRepository->value,
            defaultIndexMethod: 'getObjectClass',
        ));

    // Table naming strategy
    $services->set(TableNamingStrategyInterface::class, UnderscoreTableNamingStrategy::class);
};

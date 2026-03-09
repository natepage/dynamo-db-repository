<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Bundle\CompilerPass;

use NatePage\DynamoDbRepository\Bundle\Enum\ConfigServiceId;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigTag;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final readonly class DynamoDbClientPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(ConfigServiceId::DynamoDbClient->value) === false) {
            return;
        }

        foreach ($container->findTaggedServiceIds(ConfigTag::ObjectRepository->value) as $id => $tags) {
            $clientId = ConfigServiceId::DynamoDbClient->value;

            foreach ($tags as $tag) {
                $clientId = $tag['client'] ?? $clientId;
            }

            $definition = $container->getDefinition($id);
            $definition->setArgument('$dynamoDbClient', new Reference($clientId));
        }
    }
}

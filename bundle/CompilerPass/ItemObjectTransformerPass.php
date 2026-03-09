<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Bundle\CompilerPass;

use NatePage\DynamoDbRepository\Bundle\Enum\ConfigServiceId;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigTag;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final readonly class ItemObjectTransformerPass implements CompilerPassInterface
{
    /**
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        $transformerId = $container->hasDefinition(ConfigServiceId::ItemObjectTransformer->value)
            ? ConfigServiceId::ItemObjectTransformer->value
            : null;

        foreach ($container->findTaggedServiceIds(ConfigTag::ObjectRepository->value) as $id => $tags) {
            foreach ($tags as $tag) {
                $transformerId = $tag['item_object_transformer'] ?? $transformerId;
            }

            $definition = $container->getDefinition($id);
            $reflection = $container->getReflectionClass($definition->getClass());

            if ($transformerId && $reflection->hasMethod('setItemObjectTransformer')) {
                $definition->addMethodCall('setItemObjectTransformer', [new Reference($transformerId)]);
            }
        }
    }
}

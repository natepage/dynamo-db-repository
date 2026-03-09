<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Bundle\CompilerPass;

use NatePage\DynamoDbRepository\Bundle\Enum\ConfigParam;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigTag;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final readonly class TableNamePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds(ConfigTag::ObjectRepository->value) as $id => $tags) {
            $tableName = null;
            $tablePrefix = null;

            foreach ($tags as $tag) {
                $tableName = $tag['table_name'] ?? $tableName;
                $tablePrefix = $tag['table_prefix'] ?? $tablePrefix;
            }

            // Default to config values if not set in tag
            $tablePrefix ??= '%' . ConfigParam::TablePrefix->value . '%';

            $definition = $container->getDefinition($id);
            $definition->setArgument('$tableName', $tableName);
            $definition->setArgument('$tablePrefix', $tablePrefix);
        }
    }
}

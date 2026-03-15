<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Bundle;

use NatePage\DynamoDbRepository\Bundle\CompilerPass\DoctrineBridgePass;
use NatePage\DynamoDbRepository\Bundle\CompilerPass\DynamoDbClientPass;
use NatePage\DynamoDbRepository\Bundle\CompilerPass\ItemObjectTransformerPass;
use NatePage\DynamoDbRepository\Bundle\CompilerPass\TableNamePass;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigParam;
use NatePage\DynamoDbRepository\Bundle\Enum\ConfigTag;
use NatePage\DynamoDbRepository\Common\Repository\ObjectRepositoryInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class DynamoDbRepositoryBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new DoctrineBridgePass())
            ->addCompilerPass(new DynamoDbClientPass())
            ->addCompilerPass(new ItemObjectTransformerPass())
            ->addCompilerPass(new TableNamePass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(ObjectRepositoryInterface::class)
            ->addTag(ConfigTag::ObjectRepository->value);

        $container
            ->parameters()
            ->set(ConfigParam::TablePrefix->value, $config['table_prefix']);

        $container->import('config/services.php');

        if ($config['auto_mapper']['enabled'] ?? false) {
            $container
                ->parameters()
                ->set(ConfigParam::AutoMapperArrayAsJsonString->value, $config['auto_mapper']['array_as_json_string'])
                ->set(ConfigParam::AutoMapperDatetimeClass->value, $config['auto_mapper']['datetime_class'])
                ->set(ConfigParam::AutoMapperDatetimeFormat->value, $config['auto_mapper']['datetime_format'])
                ->set(ConfigParam::AutoMapperDefaultStringIfNull->value, $config['auto_mapper']['default_string_if_null']);

            $container->import('config/auto_mapper.php');
        }
    }
}

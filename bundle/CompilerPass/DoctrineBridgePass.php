<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Bundle\CompilerPass;

use NatePage\DynamoDbRepository\Doctrine\Registry\ManagerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;
use function Symfony\Component\String\u;

final readonly class DoctrineBridgePass implements CompilerPassInterface
{
    private const string CONFIGURATION_TAG = 'doctrine.orm.configuration';

    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(ManagerRegistry::class) === false) {
            return;
        }

        $configurations = [];
        foreach (\array_keys($container->findTaggedServiceIds(self::CONFIGURATION_TAG)) as $id) {
            $configurationName = u($id)
                ->trimPrefix('doctrine.orm.')
                ->trimSuffix('_configuration')
                ->toString();

            $configurations[$configurationName] = new Reference($id);
        }

        $definition = $container->getDefinition(ManagerRegistry::class);
        $definition
            ->setArgument('$defaultManagerName', (string) param('doctrine.default_entity_manager'))
            ->setArgument('$entityManagersServiceIds', (string) param('doctrine.entity_managers'))
            ->setArgument('$ormConfigurations', service_locator($configurations));
    }
}

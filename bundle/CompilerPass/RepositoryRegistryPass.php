<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Bundle\CompilerPass;

use NatePage\DynamoDbRepository\Bundle\Enum\ConfigTag;
use NatePage\DynamoDbRepository\Common\Registry\ObjectRepositoryRegistryInterface;
use NatePage\DynamoDbRepository\Common\Repository\ObjectRepositoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final readonly class RepositoryRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(ObjectRepositoryRegistryInterface::class) === false) {
            return;
        }

        $map = [];

        foreach ($container->findTaggedServiceIds(ConfigTag::ObjectRepository->value) as $id => $tags) {
            $repoDefinition = $container->getDefinition($id);
            $repoClass = $repoDefinition->getClass();

            if (\is_a($repoClass, ObjectRepositoryInterface::class, true) === false) {
                throw new \LogicException(sprintf(
                    'The service "%s" must implement the "%s" interface.',
                    $id,
                    ObjectRepositoryInterface::class
                ));
            }

            /** @var ObjectRepositoryInterface $repoClass */

            $reference = new Reference($id);

            foreach ([$repoClass::getObjectClass(), ...$repoClass::getObjectConcreteClasses() ?? []] as $objectClass) {
                $map[$objectClass] = $reference;
            }
        }

        $registryDefinition = $container->getDefinition(ObjectRepositoryRegistryInterface::class);
        $registryDefinition->setArgument('$repositories', ServiceLocatorTagPass::register($container, $map));
    }
}

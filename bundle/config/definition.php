<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('auto_mapper')
                ->canBeEnabled()
                ->children()
                    ->booleanNode('array_as_json_string')->defaultValue(true)->end()
                    ->scalarNode('datetime_class')->defaultValue(DateTimeImmutable::class)->end()
                    ->scalarNode('datetime_format')->defaultValue(DateTimeInterface::ATOM)->end()
                ->end()
            ->end()
            ->scalarNode('table_prefix')->defaultNull()->end()
        ->end();
};

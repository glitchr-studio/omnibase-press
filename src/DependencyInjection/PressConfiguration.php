<?php

namespace Base\Press\DependencyInjection;

use Base\Bundle\AbstractBaseConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class PressConfiguration extends AbstractBaseConfiguration
{
    private bool $childrenDeclared = false;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = $this->getTreeBuilder();
        if ($this->childrenDeclared) {
            return $treeBuilder;
        }
        $this->childrenDeclared = true;

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('contacts')
                    ->info('Who the press writes to: the agency, the label\'s PR, the artist.')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('role')->defaultNull()->info('"General management", "Press, Germany"...')->end()
                            ->scalarNode('email')->defaultNull()->end()
                            ->scalarNode('phone')->defaultNull()->end()
                            ->scalarNode('organisation')->defaultNull()->end()
                            ->scalarNode('url')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('download_kit')->defaultTrue()
                    ->info('The HD downloads at all: off, the photos are shown and none can be taken.')->end()
                ->arrayNode('bio_lengths')
                    ->info('The biographies the page shows, in this order.')
                    ->enumPrototype()->values(['short', 'medium', 'long'])->end()
                    ->defaultValue(['short', 'medium', 'long'])
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}

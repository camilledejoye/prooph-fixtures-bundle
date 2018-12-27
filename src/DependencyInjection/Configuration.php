<?php

namespace Prooph\Bundle\Fixtures\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('prooph_fixtures');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('cleaners')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->normalizeKeys(false)
                        ->children()
                            ->integerNode('batch_size')
                                ->defaultValue(10000)
                                ->info('Numbers of stream/projections names loaded at once')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

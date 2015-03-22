<?php

namespace Gos\Bundle\PubSubRouterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('gos_pubsub_router');

        $rootNode->children()
            ->arrayNode('resources')
                ->prototype('scalar')->end()
                ->end()
            ->arrayNode('loaders')
                ->prototype('scalar')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}

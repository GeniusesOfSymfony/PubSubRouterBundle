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

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('matcher_class')->defaultValue('Gos\Bundle\PubSubRouterBundle\Matcher\Matcher')->end()
                ->scalarNode('generator_class')->defaultValue('Gos\Bundle\PubSubRouterBundle\Generator\Generator')->end()
                ->scalarNode('route_loader_class')->defaultValue('Gos\Bundle\PubSubRouterBundle\Loader\RouteLoader')->end()
                ->scalarNode('router_class')->defaultValue('Gos\Bundle\PubSubRouterBundle\Router\Router')->end()
                ->arrayNode('routers')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->arrayNode('context')
                                ->children()
                                    ->scalarNode('tokenSeparator')->end()
                                ->end()
                            ->end()
                            ->arrayNode('resources')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}

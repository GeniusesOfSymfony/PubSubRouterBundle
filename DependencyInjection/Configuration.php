<?php

namespace Gos\Bundle\PubSubRouterBundle\DependencyInjection;

use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoader;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Router\Router;
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
                ->scalarNode('matcher_class')->defaultValue(Matcher::CLASS)->end()
                ->scalarNode('generator_class')->defaultValue(Generator::CLASS)->end()
                ->scalarNode('route_loader_class')->defaultValue(RouteLoader::CLASS)->end()
                ->scalarNode('router_class')->defaultValue(Router::CLASS)->end()
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

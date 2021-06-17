<?php

namespace Gos\Bundle\PubSubRouterBundle\DependencyInjection;

use Gos\Bundle\PubSubRouterBundle\Generator\CompiledGenerator;
use Gos\Bundle\PubSubRouterBundle\Matcher\CompiledMatcher;
use Gos\Bundle\PubSubRouterBundle\Router\Router;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 * @final
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('gos_pubsub_router');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('matcher_class')->defaultValue(CompiledMatcher::class)->end()
                ->scalarNode('generator_class')->defaultValue(CompiledGenerator::class)->end()
                ->scalarNode('router_class')->defaultValue(Router::class)->end()
                ->arrayNode('routers')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->arrayNode('resources')
                                ->beforeNormalization()
                                    ->ifTrue(static function ($v) {
                                        foreach ($v as $resource) {
                                            if (!\is_array($resource)) {
                                                return true;
                                            }
                                        }

                                        return false;
                                    })
                                    ->then(static function ($v) {
                                        $resources = [];

                                        foreach ($v as $resource) {
                                            if (\is_array($resource)) {
                                                $resources[] = $resource;
                                            } else {
                                                $resources[] = [
                                                    'resource' => $resource,
                                                ];
                                            }
                                        }

                                        return $resources;
                                    })
                                ->end()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('resource')
                                            ->cannotBeEmpty()
                                            ->isRequired()
                                        ->end()
                                        ->enumNode('type')
                                            ->values(['closure', 'container', 'glob', 'php', 'xml', 'yaml', null])
                                            ->defaultNull()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}

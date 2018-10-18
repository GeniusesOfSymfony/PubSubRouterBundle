<?php

namespace Gos\Bundle\PubSubRouterBundle\DependencyInjection;

use Gos\Bundle\PubSubRouterBundle\Generator\Dumper\GeneratorDumper;
use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\PhpMatcherDumper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class GosPubSubRouterExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/services'));
        $loader->load('services.yml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->setParameter('gos_pubsub_router.cache_class_prefix', $container->getParameter('kernel.container_class'));

        // <argument key="generator_cache_class">%router.cache_class_prefix%UrlGenerator</argument>
        // <argument key="matcher_cache_class">%router.cache_class_prefix%UrlMatcher</argument>
        $baseRouterOptions = [
            'cache_dir' => $container->getParameter('kernel.cache_dir'),
            'debug' => $container->getParameter('kernel.debug'),
            'generator_class' => $config['generator_class'],
            'generator_base_class' => $config['generator_class'],
            'generator_dumper_class' => GeneratorDumper::class,
            'matcher_class' => $config['matcher_class'],
            'matcher_base_class' => $config['matcher_class'],
            'matcher_dumper_class' => PhpMatcherDumper::class,
        ];

        $registryDefinition = $container->getDefinition('gos_pubsub_router.router.registry');

        foreach ($config['routers'] as $routerName => $routerConfig) {
            $routerOptions = array_merge(
                $baseRouterOptions,
                [
                    'generator_cache_class' => $container->getParameter('gos_pubsub_router.cache_class_prefix').ucfirst(strtolower($routerName)).'Generator',
                    'matcher_cache_class' => $container->getParameter('gos_pubsub_router.cache_class_prefix').ucfirst(strtolower($routerName)).'Matcher',
                ]
            );

            $serviceId = 'gos_pubsub_router.router.'.strtolower($routerName);

            $definition = new Definition(
                $config['router_class'],
                [
                    strtolower($routerName),
                    new Reference('gos_pubsub_router.routing.loader'),
                    $routerConfig['resources'],
                    $routerOptions
                ]
            );

            // Register router to the container
            $container->setDefinition($serviceId, $definition);

            // Register router to the registry
            $registryDefinition->addMethodCall('addRouter', [new Reference($serviceId)]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'gos_pubsub_router';
    }
}

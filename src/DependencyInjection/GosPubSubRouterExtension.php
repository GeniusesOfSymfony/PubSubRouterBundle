<?php

namespace Gos\Bundle\PubSubRouterBundle\DependencyInjection;

use Gos\Bundle\PubSubRouterBundle\Generator\Dumper\CompiledGeneratorDumper;
use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoaderInterface;
use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\CompiledMatcherDumper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
final class GosPubSubRouterExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->registerForAutoconfiguration(RouteLoaderInterface::class)
            ->addTag('gos_pubsub_router.routing.route_loader');

        $routerOptions = [
            'cache_dir' => $container->getParameter('kernel.cache_dir'),
            'debug' => $container->getParameter('kernel.debug'),
            'generator_class' => $config['generator_class'],
            'generator_dumper_class' => CompiledGeneratorDumper::class,
            'matcher_class' => $config['matcher_class'],
            'matcher_dumper_class' => CompiledMatcherDumper::class,
        ];

        $registryDefinition = $container->getDefinition('gos_pubsub_router.router_registry');

        foreach ($config['routers'] as $routerName => $routerConfig) {
            $lowerRouterName = strtolower($routerName);

            $serviceId = 'gos_pubsub_router.router.'.$lowerRouterName;

            $definition = new Definition(
                $config['router_class'],
                [
                    $lowerRouterName,
                    new Reference('gos_pubsub_router.routing.loader'),
                    $routerConfig['resources'],
                    $routerOptions,
                ]
            );

            // Register router to the container
            $container->setDefinition($serviceId, $definition);

            // Register router to the registry
            $registryDefinition->addMethodCall('addRouter', [new Reference($serviceId)]);
        }
    }

    public function getAlias(): string
    {
        return 'gos_pubsub_router';
    }
}

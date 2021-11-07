<?php

namespace Gos\Bundle\PubSubRouterBundle\DependencyInjection;

use Gos\Bundle\PubSubRouterBundle\Generator\Dumper\CompiledGeneratorDumper;
use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoaderInterface;
use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\CompiledMatcherDumper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 * @final
 */
class GosPubSubRouterExtension extends ConfigurableExtension
{
    /**
     * @throws InvalidArgumentException if a configured router uses a reserved name
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(RouteLoaderInterface::class)
            ->addTag('gos_pubsub_router.routing.route_loader');

        $routerOptions = [
            'cache_dir' => $container->getParameter('kernel.cache_dir'),
            'debug' => $container->getParameter('kernel.debug'),
            'generator_class' => $mergedConfig['generator_class'],
            'generator_dumper_class' => CompiledGeneratorDumper::class,
            'matcher_class' => $mergedConfig['matcher_class'],
            'matcher_dumper_class' => CompiledMatcherDumper::class,
        ];

        $registryDefinition = $container->getDefinition('gos_pubsub_router.router_registry');

        foreach ($mergedConfig['routers'] as $routerName => $routerConfig) {
            $lowerRouterName = strtolower($routerName);

            $serviceId = 'gos_pubsub_router.router.'.$lowerRouterName;

            $definition = new Definition(
                $mergedConfig['router_class'],
                [
                    $lowerRouterName,
                    new Reference('gos_pubsub_router.routing.loader'),
                    $routerConfig['resources'],
                    $routerOptions,
                ]
            );
            $definition->addMethodCall('setConfigCacheFactory', [new Reference('config_cache_factory')]);

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

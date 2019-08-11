<?php

namespace Gos\Bundle\PubSubRouterBundle\DependencyInjection;

use Gos\Bundle\PubSubRouterBundle\Generator\Dumper\PhpGeneratorDumper;
use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\PhpMatcherDumper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class GosPubSubRouterExtension extends Extension
{
    /**
     * Map containing a list of deprecated service keys where the key is the deprecated alias and the value is the new service identifier.
     */
    private const DEPRECATED_SERVICE_ALIASES = [
        'gos_pubsub_router.router.cache_warmer' => 'gos_pubsub_router.cache_warmer.router',
        'gos_pubsub_router.router.registry' => 'gos_pubsub_router.router_registry',
    ];

    /**
     * Map holding a list of router names that are reserved and cannot be used where the key is the name and the value is the reason it is reserved.
     */
    private const RESERVED_ROUTER_NAMES = [
        'cache_warmer' => 'A router cannot be named "cache_warmer" because it conflicts with the "gos_pubsub_router.router.cache_warmer" service, please use another router name.',
        'registry' => 'A router cannot be named "registry" because it conflicts with the "gos_pubsub_router.router.registry" service, please use another router name.',
    ];

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));
        $loader->load('services.yml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->setParameter('gos_pubsub_router.cache_class_prefix', $container->getParameter('kernel.container_class'));

        $baseRouterOptions = [
            'cache_dir' => $container->getParameter('kernel.cache_dir'),
            'debug' => $container->getParameter('kernel.debug'),
            'generator_class' => $config['generator_class'],
            'generator_base_class' => $config['generator_class'],
            'generator_dumper_class' => PhpGeneratorDumper::class,
            'matcher_class' => $config['matcher_class'],
            'matcher_base_class' => $config['matcher_class'],
            'matcher_dumper_class' => PhpMatcherDumper::class,
        ];

        $registryDefinition = $container->getDefinition('gos_pubsub_router.router_registry');
        $reservedRouterNames = array_keys(self::RESERVED_ROUTER_NAMES);

        foreach ($config['routers'] as $routerName => $routerConfig) {
            $lowerRouterName = strtolower($routerName);

            if (\in_array($lowerRouterName, $reservedRouterNames, true)) {
                throw new InvalidArgumentException(self::RESERVED_ROUTER_NAMES[$lowerRouterName]);
            }

            $routerOptions = array_merge(
                $baseRouterOptions,
                [
                    'generator_cache_class' => $container->getParameter('gos_pubsub_router.cache_class_prefix').ucfirst($lowerRouterName).'Generator',
                    'matcher_cache_class' => $container->getParameter('gos_pubsub_router.cache_class_prefix').ucfirst($lowerRouterName).'Matcher',
                ]
            );

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

        // Mark service aliases deprecated if able
        if (method_exists(Alias::class, 'setDeprecated')) {
            foreach (self::DEPRECATED_SERVICE_ALIASES as $deprecatedAlias => $newService) {
                if ($container->hasAlias($deprecatedAlias)) {
                    $container->getAlias($deprecatedAlias)
                        ->setDeprecated(
                            true,
                            'The "%alias_id%" service alias is deprecated and will be removed in GosPubSubRouterBundle 2.0, you should use the "'.$newService.'" service instead.'
                        );
                }
            }
        }
    }

    public function getAlias()
    {
        return 'gos_pubsub_router';
    }
}

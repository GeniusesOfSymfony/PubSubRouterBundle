<?php

namespace Gos\Bundle\PubSubRouterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config/services')
        );

        $loader->load('services.yml');

        $configuration = new Configuration();
        $configs = $this->processConfiguration($configuration, $configs);

        $configs['loaders'][] = '@gos_pubsub_router.yaml.loader';

        $routerDef = $container->getDefinition('gos_pubsub_router.router');

        foreach ($configs['loaders'] as $loaderRef) {
            $routerDef->addMethodCall('addLoader', [new Reference(ltrim($loaderRef, '@'))]);
        }

        $container->setAlias('router.pubsub', 'gos_pubsub_router.router');

        foreach ($configs['resources'] as $resource) {
            $routerDef->addMethodCall('addResource', array($resource));
        }

        $routerDef->addMethodCall('loadRoute');
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'gos_pubsub_router';
    }
}

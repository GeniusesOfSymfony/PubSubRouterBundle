<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Gos\Bundle\PubSubRouterBundle\CacheWarmer\RouterCacheWarmer;
use Gos\Bundle\PubSubRouterBundle\Command\DebugRouterCommand;
use Gos\Bundle\PubSubRouterBundle\Loader\ClosureLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\ContainerLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\GlobFileLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\PhpFileLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\XmlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\YamlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\RouterRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('gos_pubsub_router.cache_warmer.router', RouterCacheWarmer::class)
            ->args(
                [
                    service(ContainerInterface::class),
                ]
            )
            ->tag('container.service_subscriber', ['id' => 'gos_pubsub_router.router_registry'])
            ->tag('kernel.cache_warmer')

        ->set('gos_pubsub_router.command.debug_router', DebugRouterCommand::class)
            ->args(
                [
                    service('gos_pubsub_router.router_registry'),
                ]
            )
            ->tag('console.command')

        ->set('gos_pubsub_router.loader.closure', ClosureLoader::class)
            ->tag('gos_pubsub_router.routing.loader')

        ->set('gos_pubsub_router.loader.container', ContainerLoader::class)
            ->args(
                [
                    tagged_locator('gos_pubsub_router.routing.route_loader'),
                ]
            )
            ->tag('gos_pubsub_router.routing.loader')

        ->set('gos_pubsub_router.loader.glob', GlobFileLoader::class)
            ->args(
                [
                    service('file_locator'),
                ]
            )
            ->tag('gos_pubsub_router.routing.loader')

        ->set('gos_pubsub_router.loader.php', PhpFileLoader::class)
            ->args(
                [
                    service('file_locator'),
                ]
            )
            ->tag('gos_pubsub_router.routing.loader')

        ->set('gos_pubsub_router.loader.xml', XmlFileLoader::class)
            ->args(
                [
                    service('file_locator'),
                ]
            )
            ->tag('gos_pubsub_router.routing.loader')

        ->set('gos_pubsub_router.loader.yaml', YamlFileLoader::class)
            ->args(
                [
                    service('file_locator'),
                ]
            )
            ->tag('gos_pubsub_router.routing.loader')

        ->set('gos_pubsub_router.router_registry', RouterRegistry::class)
            ->alias(RouterRegistry::class, 'gos_pubsub_router.router_registry')

        ->set('gos_pubsub_router.routing.loader', DelegatingLoader::class)
            ->args(
                [
                    service('gos_pubsub_router.routing.resolver'),
                ]
            )

        ->set('gos_pubsub_router.routing.resolver', LoaderResolver::class)
    ;
};

<?php

namespace Gos\Bundle\PubSubRouterBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds tagged gos_pubsub_router.routing.loader services to gos_pubsub_router.routing.resolver service.
 */
final class RoutingResolverPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasDefinition('gos_pubsub_router.routing.resolver')) {
            return;
        }

        $definition = $container->getDefinition('gos_pubsub_router.routing.resolver');

        foreach ($this->findAndSortTaggedServices('gos_pubsub_router.routing.loader', $container) as $id) {
            $definition->addMethodCall('addLoader', [new Reference($id)]);
        }
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle;

use Gos\Bundle\PubSubRouterBundle\DependencyInjection\CompilerPass\RouterCompilerPass;
use Gos\Bundle\PubSubRouterBundle\DependencyInjection\GosPubSubRouterExtension;
use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class GosPubSubRouterBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $registeredRouter = $this->container->getParameter('gos_pubsub_registered_routers');

        foreach ($registeredRouter as $routerType) {
            $routeCollection = $this->container->get('gos_pubsub_router.collection.' . $routerType);

            /** @var RouteLoader $routeLoader */
            $routeLoader = $this->container->get('gos_pubsub_router.loader.' . $routerType);
            $routeLoader->load($routeCollection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RouterCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new GosPubSubRouterExtension();
    }
}

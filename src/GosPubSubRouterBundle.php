<?php

namespace Gos\Bundle\PubSubRouterBundle;

use Gos\Bundle\PubSubRouterBundle\DependencyInjection\CompilerPass\RoutingResolverPass;
use Gos\Bundle\PubSubRouterBundle\DependencyInjection\GosPubSubRouterExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class GosPubSubRouterBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RoutingResolverPass());
    }

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new GosPubSubRouterExtension();
        }

        return parent::getContainerExtension();
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}

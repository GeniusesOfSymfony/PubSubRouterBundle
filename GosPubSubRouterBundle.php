<?php

namespace Gos\Bundle\PubSubRouterBundle;

use Gos\Bundle\PubSubRouterBundle\DependencyInjection\CompilerPass\RouterCompilerPass;
use Gos\Bundle\PubSubRouterBundle\DependencyInjection\GosPubSubRouterExtension;
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
        if (null === $this->extension) {
            $this->extension = new GosPubSubRouterExtension();
        }

        return parent::getContainerExtension();
    }
}

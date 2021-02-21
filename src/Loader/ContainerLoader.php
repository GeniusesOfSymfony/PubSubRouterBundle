<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Psr\Container\ContainerInterface;

final class ContainerLoader extends ObjectLoader
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param mixed $resource
     */
    public function supports($resource, string $type = null): bool
    {
        return 'service' === $type;
    }

    protected function getObject(string $id): object
    {
        return $this->container->get($id);
    }
}

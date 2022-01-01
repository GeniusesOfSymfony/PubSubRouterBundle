<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Psr\Container\ContainerInterface;

final class ContainerLoader extends ObjectLoader
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return 'service' === $type;
    }

    protected function getObject(string $id): object
    {
        return $this->container->get($id);
    }
}

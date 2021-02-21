<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Config\Loader\Loader;

final class ClosureLoader extends Loader
{
    /**
     * @param mixed $resource
     *
     * @throws \LogicException if the loader does not return a RouteCollection
     */
    public function load($resource, string $type = null): RouteCollection
    {
        $routeCollection = $resource();

        if (!$routeCollection instanceof RouteCollection) {
            throw new \LogicException(sprintf('Route loaders must return a RouteCollection: %s returned', get_debug_type($routeCollection)));
        }

        return $routeCollection;
    }

    /**
     * @param mixed $resource
     */
    public function supports($resource, string $type = null): bool
    {
        return $resource instanceof \Closure && (!$type || 'closure' === $type);
    }
}

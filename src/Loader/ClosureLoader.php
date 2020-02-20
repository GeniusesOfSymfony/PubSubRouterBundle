<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

final class ClosureLoader extends CompatibilityLoader
{
    /**
     * @param mixed $closure
     *
     * @throws \LogicException if the loader does not return a RouteCollection
     */
    protected function doLoad($closure, string $type = null): RouteCollection
    {
        $routeCollection = $closure();

        if (!$routeCollection instanceof RouteCollection) {
            $type = \is_object($routeCollection) ? \get_class($routeCollection) : \gettype($routeCollection);

            throw new \LogicException(sprintf('Route loaders must return a RouteCollection: %s returned', $type));
        }

        return $routeCollection;
    }

    /**
     * @param mixed $resource
     */
    protected function doSupports($resource, string $type = null): bool
    {
        return $resource instanceof \Closure && (!$type || 'closure' === $type);
    }
}

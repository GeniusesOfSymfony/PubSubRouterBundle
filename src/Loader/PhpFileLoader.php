<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\Configurator\RoutingConfigurator;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Config\Resource\FileResource;

/**
 * @final
 */
class PhpFileLoader extends CompatibilityFileLoader
{
    /**
     * @param mixed $resource
     *
     * @throws \LogicException if the resource does not return a RouteCollection
     */
    protected function doLoad($resource, string $type = null): RouteCollection
    {
        $path = $this->locator->locate($resource);
        $this->setCurrentDir(\dirname($path));

        // The closure forbids access to the private scope in the included file
        $loader = $this;
        $load = \Closure::bind(static function ($file) use ($loader) {
            return include $file;
        }, null, $this->createProtectedLoader());

        $result = $load($path);

        if (\is_callable($result)) {
            $collection = $this->callConfigurator($result, $path, $resource);
        } elseif ($result instanceof RouteCollection) {
            $collection = $result;
        } else {
            throw new \LogicException(sprintf('The %s file must return a callback or a RouteCollection: %s returned', $path, get_debug_type($result)));
        }

        $collection->addResource(new FileResource($path));

        return $collection;
    }

    /**
     * @param mixed $resource
     */
    protected function doSupports($resource, string $type = null): bool
    {
        return \is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'php' === $type);
    }

    private function callConfigurator(callable $result, string $path, string $file): RouteCollection
    {
        $collection = new RouteCollection();

        $result(new RoutingConfigurator($collection, $this, $path, $file));

        return $collection;
    }

    private function createProtectedLoader(): self
    {
        return new class($this->getLocator()) extends PhpFileLoader {};
    }
}

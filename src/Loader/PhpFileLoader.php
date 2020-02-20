<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

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

        if (!($result instanceof RouteCollection)) {
            $type = \is_object($result) ? \get_class($result) : \gettype($result);

            throw new \LogicException(sprintf('The %s file must return a callback or a RouteCollection: %s returned', $path, $type));
        }

        $collection = $result;

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

    private function createProtectedLoader(): self
    {
        return new class($this->getLocator()) extends PhpFileLoader {};
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Config\Resource\FileResource;

abstract class ObjectLoader extends CompatibilityLoader
{
    abstract protected function getObject(string $id): object;

    /**
     * @param mixed $resource
     *
     * @throws \BadMethodCallException   if the method does not exist on the loader object
     * @throws \InvalidArgumentException if the resource cannot be processed
     * @throws \LogicException           if the loader does not return a RouteCollection
     */
    protected function doLoad($resource, string $type = null): RouteCollection
    {
        if (!preg_match('/^[^\:]+(?:::(?:[^\:]+))?$/', $resource)) {
            throw new \InvalidArgumentException(sprintf('Invalid resource "%s" passed to the %s route loader: use the format "object_id::method" or "object_id" if your object class has an "__invoke" method.', $resource, \is_string($type) ? '"'.$type.'"' : 'object'));
        }

        $parts = explode('::', $resource);
        $method = $parts[1] ?? '__invoke';

        $loaderObject = $this->getObject($parts[0]);

        if (!\is_callable([$loaderObject, $method])) {
            throw new \BadMethodCallException(sprintf('Method "%s" not found on "%s" when importing routing resource "%s"', $method, \get_class($loaderObject), $resource));
        }

        $routeCollection = $loaderObject->$method($this);

        if (!$routeCollection instanceof RouteCollection) {
            $type = \is_object($routeCollection) ? \get_class($routeCollection) : \gettype($routeCollection);

            throw new \LogicException(sprintf('The %s::%s method must return a RouteCollection: %s returned', \get_class($loaderObject), $method, $type));
        }

        $this->addClassResource(new \ReflectionClass($loaderObject), $routeCollection);

        return $routeCollection;
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function addClassResource(\ReflectionClass $class, RouteCollection $collection): void
    {
        do {
            if (false !== $class->getFileName() && is_file($class->getFileName())) {
                $collection->addResource(new FileResource($class->getFileName()));
            }
        } while ($class = $class->getParentClass());
    }
}

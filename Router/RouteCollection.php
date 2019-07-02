<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class RouteCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var Route[]
     */
    protected $routes = [];

    /**
     * @var ResourceInterface[]
     */
    private $resources = [];

    /**
     * @param Route[] $routes
     */
    public function __construct(array $routes = [])
    {
        foreach ($routes as $routeName => $route) {
            $this->add($routeName, $route);
        }
    }

    public function __clone()
    {
        /** @var Route $route */
        foreach ($this->routes as $name => $route) {
            $this->routes[$name] = clone $route;
        }
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->routes);
    }

    /**
     * @return int
     */
    public function count()
    {
        return \count($this->routes);
    }

    public function add(string $name, Route $route): void
    {
        $this->routes[$name] = $route;
    }

    /**
     * @return Route[]
     */
    public function all(): array
    {
        return $this->routes;
    }

    public function get(string $name): ?Route
    {
        return $this->routes[$name] ?? null;
    }

    /**
     * @param string|string[] $name
     */
    public function remove($name): void
    {
        foreach ((array) $name as $n) {
            unset($this->routes[$n]);
        }
    }

    /**
     * @param RouteCollection $collection
     */
    public function addCollection(RouteCollection $collection)
    {
        // we need to remove all routes with the same names first because just replacing them
        // would not place the new route at the end of the merged array
        foreach ($collection->all() as $name => $route) {
            unset($this->routes[$name]);
            $this->routes[$name] = $route;
        }

        foreach ($collection->getResources() as $resource) {
            $this->addResource($resource);
        }
    }

    /**
     * @return ResourceInterface[] An array of resources
     */
    public function getResources(): array
    {
        return array_values($this->resources);
    }

    /**
     * Adds a resource for this collection. If the resource already exists it is not added.
     */
    public function addResource(ResourceInterface $resource): void
    {
        $key = (string) $resource;

        if (!isset($this->resources[$key])) {
            $this->resources[$key] = $resource;
        }
    }
}

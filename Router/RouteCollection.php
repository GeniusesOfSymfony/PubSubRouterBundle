<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class RouteCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var RouteInterface[]
     */
    protected $routes = [];

    /**
     * @var ResourceInterface[]
     */
    private $resources = [];

    /**
     * @param array $data
     *
     * @return RouteCollection
     */
    public static function __set_state($data)
    {
        return new self($data['routes']);
    }

    /**
     * @param RouteInterface[] $routes
     */
    public function __construct(array $routes = [])
    {
        foreach ($routes as $routeName => $route) {
            $this->add($routeName, $route);
        }
    }

    public function __clone()
    {
        /**
         * @var string $name
         * @var RouteInterface $route
         */
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
        return count($this->routes);
    }

    /**
     * @param string         $name
     * @param RouteInterface $route
     */
    public function add($name, RouteInterface $route)
    {
        $this->routes[$name] = $route;
    }

    /**
     * @return RouteInterface[]
     */
    public function all()
    {
        return $this->routes;
    }

    /**
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function get($name)
    {
        return isset($this->routes[$name]) ? $this->routes[$name] : null;
    }

    /**
     * @param string $name
     */
    public function remove($name)
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
    public function getResources()
    {
        return array_values($this->resources);
    }

    /**
     * Adds a resource for this collection. If the resource already exists it is not added.
     */
    public function addResource(ResourceInterface $resource)
    {
        $key = (string) $resource;

        if (!isset($this->resources[$key])) {
            $this->resources[$key] = $resource;
        }
    }
}

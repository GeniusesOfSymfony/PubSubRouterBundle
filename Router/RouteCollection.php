<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class RouteCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var RouteInterface[]
     */
    protected $routes;

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
    public function __construct(Array $routes = null)
    {
        if (null !== $routes) {
            foreach ($routes as $routeName => $route) {
                $this->add($routeName, $route);
            }
        } else {
            $this->routes = [];
        }
    }

    public function __clone()
    {
        /*
         * @var string
         * @var RouteInterface
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
     * @param string $name
     */
    public function remove($name)
    {
        foreach ((array) $name as $n) {
            unset($this->routes[$n]);
        }
    }

    /**
     * @param string $name
     *
     * @return Route|null
     */
    public function get($name)
    {
        if (isset($this->routes[$name])) {
            $route = $this->routes[$name];
            $route->setName($name);

            return $route;
        }

        return false;
    }

    /**
     * @return Route[]
     */
    public function all()
    {
        return $this->routes;
    }

    /**
     * @param RouteCollection $collection
     */
    public function addCollection(RouteCollection $collection)
    {
        foreach ($collection->all() as $name => $route) {
            $this->add($name, $route);
        }
    }
}

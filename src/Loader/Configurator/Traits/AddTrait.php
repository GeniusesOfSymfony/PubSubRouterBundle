<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader\Configurator\Traits;

use Gos\Bundle\PubSubRouterBundle\Loader\Configurator\CollectionConfigurator;
use Gos\Bundle\PubSubRouterBundle\Loader\Configurator\RouteConfigurator;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

trait AddTrait
{
    /**
     * @var RouteCollection
     */
    private $collection;

    /**
     * @var string
     */
    private $name = '';

    /**
     * Adds a route.
     *
     * @param callable|string $callback A callable function that handles this route or a string to be used with a service locator
     */
    public function __invoke(string $name, string $pattern, $callback): RouteConfigurator
    {
        return $this->add($name, $pattern, $callback);
    }

    /**
     * Adds a route.
     *
     * @param callable|string $callback A callable function that handles this route or a string to be used with a service locator
     */
    public function add(string $name, string $pattern, $callback): RouteConfigurator
    {
        $parentConfigurator = $this instanceof CollectionConfigurator ? $this : ($this instanceof RouteConfigurator ? $this->parentConfigurator : null);
        $route = $this->createRoute($this->collection, $name, $pattern, $callback);

        return new RouteConfigurator($this->collection, $route, $this->name, $parentConfigurator);
    }

    /**
     * Creates a routes.
     *
     * @param callable|string $callback A callable function that handles this route or a string to be used with a service locator
     */
    final protected function createRoute(RouteCollection $collection, string $name, string $pattern, $callback): RouteCollection
    {
        $routes = new RouteCollection();
        $routes->add($name, $route = new Route($pattern, $callback));
        $collection->add($name, $route);

        return $routes;
    }
}

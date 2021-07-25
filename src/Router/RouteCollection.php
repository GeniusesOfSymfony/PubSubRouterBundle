<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 *
 * @implements \IteratorAggregate<Route>
 */
final class RouteCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var array<string, Route>
     */
    private array $routes = [];

    /**
     * @var array<string, ResourceInterface>
     */
    private array $resources = [];

    /**
     * @param array<string, Route> $routes
     */
    public function __construct(array $routes = [])
    {
        foreach ($routes as $routeName => $route) {
            $this->add($routeName, $route);
        }
    }

    public function __clone()
    {
        foreach ($this->routes as $name => $route) {
            $this->routes[$name] = clone $route;
        }
    }

    /**
     * @return \ArrayIterator<string, Route>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->routes);
    }

    public function count(): int
    {
        return \count($this->routes);
    }

    public function add(string $name, Route $route): void
    {
        $this->routes[$name] = $route;
    }

    /**
     * @return array<string, Route>
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
    public function remove(string | array $name): void
    {
        foreach ((array) $name as $n) {
            unset($this->routes[$n]);
        }
    }

    public function addCollection(self $collection): void
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
     * Adds defaults to all routes.
     *
     * An existing default value under the same name in a route will be overridden.
     *
     * @param array<string, mixed> $defaults
     */
    public function addDefaults(array $defaults): void
    {
        if ($defaults) {
            foreach ($this->routes as $route) {
                $route->addDefaults($defaults);
            }
        }
    }

    /**
     * Adds requirements to all routes.
     *
     * An existing requirement under the same name in a route will be overridden.
     *
     * @param array<string, string> $requirements
     */
    public function addRequirements(array $requirements): void
    {
        if ($requirements) {
            foreach ($this->routes as $route) {
                $route->addRequirements($requirements);
            }
        }
    }

    /**
     * Adds options to all routes.
     *
     * An existing option value under the same name in a route will be overridden.
     *
     * @param array<string, mixed> $options
     */
    public function addOptions(array $options): void
    {
        if ($options) {
            foreach ($this->routes as $route) {
                $route->addOptions($options);
            }
        }
    }

    /**
     * @return ResourceInterface[]
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

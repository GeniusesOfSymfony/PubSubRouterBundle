<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

final class RouterRegistry
{
    /**
     * @var RouterInterface[]
     */
    private $routers = [];

    /**
     * @throws \RuntimeException if a router is already registered with the given router's name
     */
    public function addRouter(RouterInterface $router): void
    {
        if (isset($this->routers[$router->getName()])) {
            throw new \RuntimeException(sprintf('A router named "%s" is already registered.', $router->getName()));
        }

        $this->routers[$router->getName()] = $router;
    }

    /**
     * @throws \InvalidArgumentException if the requested router was not registered
     */
    public function getRouter(string $name): RouterInterface
    {
        if (!$this->hasRouter($name)) {
            throw new \InvalidArgumentException(sprintf('A router named "%s" has not been registered.', $name));
        }

        return $this->routers[$name];
    }

    /**
     * @return RouterInterface[]
     */
    public function getRouters(): array
    {
        return $this->routers;
    }

    public function hasRouter(string $name): bool
    {
        return isset($this->routers[$name]);
    }
}

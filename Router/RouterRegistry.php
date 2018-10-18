<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

final class RouterRegistry
{
    /**
     * @var RouterInterface[]
     */
    private $routers = [];

    public function addRouter(RouterInterface $router)
    {
        if (isset($this->routers[$router->getName()])) {
            throw new \RuntimeException(sprintf('A router named "%s" is already registered.', $router->getName()));
        }

        $this->routers[$router->getName()] = $router;
    }

    /**
     * @param string $name
     *
     * @return RouterInterface
     */
    public function getRouter($name)
    {
        if (!$this->hasRouter($name)) {
            throw new \InvalidArgumentException(sprintf('A router named "%s" has not been registered.', $name));
        }

        return $this->routers[$name];
    }

    /**
     * @return RouterInterface[]
     */
    public function getRouters()
    {
        return $this->routers;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasRouter($name)
    {
        return isset($this->routers[$name]);
    }
}

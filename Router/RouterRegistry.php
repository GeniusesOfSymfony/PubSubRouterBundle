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
     * @return RouterInterface[]
     */
    public function getRouters()
    {
        return $this->routers;
    }
}

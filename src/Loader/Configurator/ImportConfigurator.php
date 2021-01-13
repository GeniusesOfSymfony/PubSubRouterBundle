<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader\Configurator;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

final class ImportConfigurator
{
    use Traits\RouteTrait;

    /**
     * @var RouteCollection
     */
    private $parent;

    public function __construct(RouteCollection $parent, RouteCollection $route)
    {
        $this->parent = $parent;
        $this->route = $route;
    }

    public function __sleep()
    {
        throw new \BadMethodCallException('Cannot serialize '.self::class);
    }

    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize '.self::class);
    }

    public function __destruct()
    {
        $this->parent->addCollection($this->route);
    }
}

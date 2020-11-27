<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator\Dumper;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

abstract class GeneratorDumper implements GeneratorDumperInterface
{
    private RouteCollection $routes;

    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }
}

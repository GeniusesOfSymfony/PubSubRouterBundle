<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher\Dumper;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

abstract class MatcherDumper implements MatcherDumperInterface
{
    public function __construct(private readonly RouteCollection $routes)
    {
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }
}

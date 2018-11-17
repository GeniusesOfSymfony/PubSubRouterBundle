<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher\Dumper;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

abstract class MatcherDumper implements MatcherDumperInterface
{
    /**
     * @var RouteCollection
     */
    private $routes;

    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }
}

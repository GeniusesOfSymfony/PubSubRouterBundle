<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

interface RouteCompilerInterface
{
    /**
     * @throws \LogicException If the Route cannot be compiled because the pattern is invalid
     */
    public static function compile(Route $route): CompiledRoute;
}

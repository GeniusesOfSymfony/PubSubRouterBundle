<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

interface RouteCompilerInterface
{
    /**
     * Compiles the current route instance.
     *
     * @return CompiledRoute
     *
     * @throws \LogicException If the Route cannot be compiled because the pattern is invalid
     */
    public static function compile(Route $route);
}

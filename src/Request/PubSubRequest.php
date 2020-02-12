<?php

namespace Gos\Bundle\PubSubRouterBundle\Request;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @final
 */
class PubSubRequest
{
    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var Route
     */
    protected $route;

    /**
     * @var ParameterBag
     */
    protected $attributes;

    public function __construct(string $routeName, Route $route, array $attributes)
    {
        $this->attributes = new ParameterBag($attributes);
        $this->route = $route;
        $this->routeName = $routeName;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getRoute(): Route
    {
        return $this->route;
    }

    public function getAttributes(): ParameterBag
    {
        return $this->attributes;
    }
}

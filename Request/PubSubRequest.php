<?php

namespace Gos\Bundle\PubSubRouterBundle\Request;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Symfony\Component\HttpFoundation\ParameterBag;

class PubSubRequest
{
    /** @var  string */
    protected $routeName;

    /** @var  Route */
    protected $route;

    /** @var  ParameterBag */
    protected $attributes;

    /**
     * @param string $routeName
     * @param Route  $route
     * @param array  $attributes
     */
    public function __construct($routeName, $route, $attributes)
    {
        $this->attributes = new ParameterBag($attributes);
        $this->route = $route;
        $this->routeName = $routeName;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return ParameterBag
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}

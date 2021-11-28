<?php

namespace Gos\Bundle\PubSubRouterBundle\Request;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Symfony\Component\HttpFoundation\ParameterBag;

final class PubSubRequest
{
    public readonly ParameterBag $attributes;

    public function __construct(public readonly string $routeName, public readonly Route $route, array $attributes)
    {
        $this->attributes = new ParameterBag($attributes);
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

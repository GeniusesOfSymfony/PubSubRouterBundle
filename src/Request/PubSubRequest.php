<?php

namespace Gos\Bundle\PubSubRouterBundle\Request;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Symfony\Component\HttpFoundation\ParameterBag;

final class PubSubRequest
{
    public readonly ParameterBag $attributes;

    public function __construct(
        public readonly string $routeName,
        public readonly Route $route,
        array $attributes
    ) {
        $this->attributes = new ParameterBag($attributes);
    }

    /**
     * @deprecated to be removed in 4.0, read the route name from the `$routeName` property instead
     */
    public function getRouteName(): string
    {
        trigger_deprecation('gos/pubsub-router-bundle', '3.0', 'The %s() method is deprecated and will be removed in 4.0. Read the route name from the $routeName property instead.', __METHOD__);

        return $this->routeName;
    }

    /**
     * @deprecated to be removed in 4.0, fetch the route from the `$route` property instead
     */
    public function getRoute(): Route
    {
        trigger_deprecation('gos/pubsub-router-bundle', '3.0', 'The %s() method is deprecated and will be removed in 4.0. Fetch the route from the $route property instead.', __METHOD__);

        return $this->route;
    }

    /**
     * @deprecated to be removed in 4.0, read the attributes from the `$attributes` property instead
     */
    public function getAttributes(): ParameterBag
    {
        trigger_deprecation('gos/pubsub-router-bundle', '3.0', 'The %s() method is deprecated and will be removed in 4.0. Read the attributes from the $attributes property instead.', __METHOD__);

        return $this->attributes;
    }
}

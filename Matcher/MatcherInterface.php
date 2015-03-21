<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
interface MatcherInterface
{
    /**
     * @param string          $channel
     * @param RouteCollection $routeCollection
     * @param string          $tokenSeparator
     *
     * @return mixed
     *
     * @throws ResourceNotFoundException
     */
    public function match($channel, RouteCollection $routeCollection, $tokenSeparator);

    /**
     * @param Route  $route
     * @param string $expected
     *
     * @return bool
     */
    public function compare(Route $route, $expected, $tokenSeparator);
}

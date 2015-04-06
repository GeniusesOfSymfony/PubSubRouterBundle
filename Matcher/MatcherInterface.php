<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
interface MatcherInterface
{
    /**
     * @param string $channel
     * @param string $tokenSeparator
     *
     * @return bool
     *
     * @throws ResourceNotFoundException
     */
    public function match($channel, $tokenSeparator = null);

    /**
     * @param RouteCollection $collection
     */
    public function setCollection(RouteCollection $collection);
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
interface RouterInterface
{
    /**
     * @param RouterContext $context
     */
    public function setContext(RouterContext $context);

    /**
     * @return RouterContext
     */
    public function getContext();

    /**
     * @param string      $channel
     * @param null|string $tokenSeparator
     */
    public function match($channel, $tokenSeparator = null);

    /**
     * @param string $resource
     */
    public function addResource($resource);

    public function loadRoute();

    /**
     * @return bool
     */
    public function isLoaded();

    /**
     * @return RouteCollection
     */
    public function getCollection();
}

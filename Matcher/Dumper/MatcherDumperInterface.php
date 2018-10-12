<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher\Dumper;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

interface MatcherDumperInterface
{
    /**
     * @param array $options
     *
     * @return string
     */
    public function dump(array $options = []);

    /**
     * @return RouteCollection
     */
    public function getRoutes();
}

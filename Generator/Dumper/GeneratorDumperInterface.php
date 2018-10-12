<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator\Dumper;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

interface GeneratorDumperInterface
{
    /**
     * @param array $options
     *
     * @return string
     */
    public function dump(array $options = array());

    /**
     * @return RouteCollection
     */
    public function getRoutes();
}

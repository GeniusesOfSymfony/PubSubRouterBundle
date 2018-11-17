<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator\Dumper;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

interface GeneratorDumperInterface
{
    public function dump(array $options = []): string;

    public function getRoutes(): RouteCollection;
}

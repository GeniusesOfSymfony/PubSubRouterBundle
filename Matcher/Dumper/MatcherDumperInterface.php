<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher\Dumper;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

interface MatcherDumperInterface
{
    public function dump(array $options = []): string;

    public function getRoutes(): RouteCollection;
}

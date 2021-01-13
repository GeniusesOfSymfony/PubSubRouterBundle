<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Matcher;

use Gos\Bundle\PubSubRouterBundle\Matcher\CompiledMatcher;
use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\CompiledMatcherDumper;
use Gos\Bundle\PubSubRouterBundle\Matcher\MatcherInterface;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

final class CompiledMatcherTest extends MatcherTest
{
    protected function getMatcher(RouteCollection $routes): MatcherInterface
    {
        $dumper = new CompiledMatcherDumper($routes);

        return new CompiledMatcher($dumper->getCompiledRoutes());
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Matcher\CompiledMatcher;
use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\CompiledMatcherDumper;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Matcher\MatcherInterface;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;

final class CompiledMatcherTest extends MatcherTest
{
    protected function getMatcher(RouteCollection $routes): MatcherInterface
    {
        $dumper = new CompiledMatcherDumper($routes);

        return new CompiledMatcher($dumper->getCompiledRoutes());
    }
}

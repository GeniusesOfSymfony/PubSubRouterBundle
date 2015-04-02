<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoader;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

class RouteLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testAddResources()
    {
        $collection = $this->prophesize(RouteCollection::CLASS);
        $routeLoader = new RouteLoader($collection->reveal(), '', true);
        $routeLoader->addResource('foo');
        $this->assertEquals(['foo'], \PHPUnit_Framework_Assert::readAttribute($routeLoader, 'resources'));
        $routeLoader->addResource('bar');
        $this->assertEquals(['foo', 'bar'], \PHPUnit_Framework_Assert::readAttribute($routeLoader, 'resources'));
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\AbstractRouteLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoader;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Tests\PubSubTestCase;
use Symfony\Component\Config\ConfigCache;

class RouteLoaderTest extends PubSubTestCase
{
    public function testAddResources()
    {
        $collection = $this->prophesize(RouteCollection::CLASS);
        $routeLoader = new RouteLoader($collection->reveal(), '', true);
        $routeLoader->addResource('foo');
        $this->assertEquals(['foo'], $this->readProperty($routeLoader, 'resources'));
        $routeLoader->addResource('bar');
        $this->assertEquals(['foo', 'bar'], $this->readProperty($routeLoader, 'resources'));
    }

    public function testAddLoader()
    {
        $collection = $this->prophesize(RouteCollection::CLASS);
        $loader = $this->prophesize(AbstractRouteLoader::CLASS)->reveal();

        $routeLoader = new RouteLoader($collection->reveal(), '', true);
        $routeLoader->addLoader($loader);
        $this->assertEquals([$loader], $this->readProperty($routeLoader, 'loaders'));

        $loader2 = $this->prophesize(AbstractRouteLoader::CLASS)->reveal();
        $routeLoader->addLoader($loader2);
        $this->assertEquals([$loader, $loader2], $this->readProperty($routeLoader, 'loaders'));
    }

    public function testConstruct()
    {
        $collection = $this->prophesize(RouteCollection::CLASS)->reveal();
        $routeLoader = new RouteLoader($collection, '/foo', true);

        $this->assertEquals([], $this->readProperty($routeLoader, 'loaders'));
        $this->assertEquals([], $this->readProperty($routeLoader, 'resources'));
        $this->assertEquals($collection, $this->readProperty($routeLoader, 'routeCollection'));
        $this->assertEquals('/foo', $this->readProperty($routeLoader, 'cacheDir'));
        $this->assertEquals(true, $this->readProperty($routeLoader, 'debug'));
        $this->assertEquals('/foo/' . RouteLoader::CACHE_FILE_NAME, $this->readProperty($routeLoader, 'fileName'));

        $configCache = $this->readProperty($routeLoader, 'cache');

        $this->assertInstanceOf(ConfigCache::CLASS, $configCache);
        $this->assertEquals(true, $this->readProperty($configCache, 'debug'));
        $this->assertEquals('/foo/' . RouteLoader::CACHE_FILE_NAME, $this->readProperty($configCache, 'file'));
    }
}

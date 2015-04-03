<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\AbstractRouteLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoader;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Config\ConfigCache;

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

    public function testAddLoader()
    {
        $collection = $this->prophesize(RouteCollection::CLASS);
        $loader = $this->prophesize(AbstractRouteLoader::CLASS)->reveal();

        $routeLoader = new RouteLoader($collection->reveal(), '', true);
        $routeLoader->addLoader($loader);
        $this->assertEquals([$loader], \PHPUnit_Framework_Assert::readAttribute($routeLoader, 'loaders'));

        $loader2 = $this->prophesize(AbstractRouteLoader::CLASS)->reveal();
        $routeLoader->addLoader($loader2);
        $this->assertEquals([$loader, $loader2], \PHPUnit_Framework_Assert::readAttribute($routeLoader, 'loaders'));
    }

    public function testConstruct()
    {
        $collection = $this->prophesize(RouteCollection::CLASS)->reveal();
        $routeLoader = new RouteLoader($collection, '/foo', true);
        $this->assertEquals($collection, \PHPUnit_Framework_Assert::readAttribute($routeLoader, 'routeCollection'));
        $this->assertEquals('/foo', \PHPUnit_Framework_Assert::readAttribute($routeLoader, 'cacheDir'));
        $this->assertEquals(true, \PHPUnit_Framework_Assert::readAttribute($routeLoader, 'debug'));
        $this->assertEquals('/foo/'.RouteLoader::CACHE_FILE_NAME, \PHPUnit_Framework_Assert::readAttribute($routeLoader, 'fileName'));
        $configCache = \PHPUnit_Framework_Assert::readAttribute($routeLoader, 'cache');
        $this->assertInstanceOf(ConfigCache::CLASS, $configCache);
        $this->assertEquals(true, \PHPUnit_Framework_Assert::readAttribute($configCache, 'debug'));
        $this->assertEquals('/foo/'.RouteLoader::CACHE_FILE_NAME, \PHPUnit_Framework_Assert::readAttribute($configCache, 'file'));
    }

    public function testLoad()
    {
        
    }
}

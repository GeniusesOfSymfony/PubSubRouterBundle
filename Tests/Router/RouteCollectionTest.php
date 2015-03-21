<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

class RouteCollectionTest extends \PHPUnit_Framework_TestCase
{
    protected $routes;

    public function setUp()
    {
        $this->routes = [
            'routeA' => new Route(
                'channel/abc',
                [['callable' => 'Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'], 'args' => ['gos_redis', 'gos_websocket']],
                ['uid' => "\d+", 'wildcard' => true]
            ),
            'routeB' => new Route(
                'channel/cde',
                [['callable' => 'Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'], 'args' => ['gos_redis', 'gos_websocket']],
                ['uid' => "\d+", 'wildcard' => true]
            ),
            'routeC' => new Route(
                'channel/foo/bar',
                [['callable' => 'Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'], 'args' => ['gos_redis', 'gos_websocket']],
                ['uid' => "\d+", 'wildcard' => true]
            ),
        ];
    }

    public function testConstructorWithInitialRoutes()
    {
        $routeCollection = new RouteCollection($this->routes);
        $this->assertEquals($this->routes, \PHPUnit_Framework_Assert::readAttribute($routeCollection, 'routes'));
    }

    public function testClone()
    {
        $routeCollectionA = new RouteCollection($this->routes);
        $routeCollectionB = clone $routeCollectionA;

        $this->assertEquals(
            \PHPUnit_Framework_Assert::readAttribute($routeCollectionA, 'routes'),
            \PHPUnit_Framework_Assert::readAttribute($routeCollectionB, 'routes')
        );
    }

    public function testCount()
    {
        $routeCollection = new RouteCollection($this->routes);
        $this->assertCount(3, \PHPUnit_Framework_Assert::readAttribute($routeCollection, 'routes'));
    }

    public function testAdd()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('routeA', $this->routes['routeA']);

        $this->assertEquals(['routeA' => $this->routes['routeA']], \PHPUnit_Framework_Assert::readAttribute($routeCollection, 'routes'));

        $routeCollection->add('routeB', $this->routes['routeB']);

        $this->assertEquals([
            'routeA' => $this->routes['routeA'],
            'routeB' => $this->routes['routeB'],
        ], \PHPUnit_Framework_Assert::readAttribute($routeCollection, 'routes'));
    }

    public function testRemove()
    {
        $routeCollection = new RouteCollection($this->routes);

        $routeCollection->remove('routeA');

        $this->assertEquals([
            'routeB' => $this->routes['routeB'],
            'routeC' => $this->routes['routeC'],
        ], \PHPUnit_Framework_Assert::readAttribute($routeCollection, 'routes'));
    }

    public function testGet()
    {
        $routeCollection = new RouteCollection($this->routes);
        $this->assertEquals($this->routes['routeA'], $routeCollection->get('routeA'));
    }

    public function testAll()
    {
        $routeCollection = new RouteCollection($this->routes);

        $this->assertEquals($this->routes, $routeCollection->all());
    }

    public function testAddCollection()
    {
        $routeCollectionA = new RouteCollection([
            'routeA' => $this->routes['routeA'],
            'routeB' => $this->routes['routeB'],
        ]);

        $routeCollectionB = new RouteCollection([
            'routeC' => $this->routes['routeC'],
        ]);

        $routeCollectionA->addCollection($routeCollectionB);

        $this->assertEquals($this->routes, \PHPUnit_Framework_Assert::readAttribute($routeCollectionA, 'routes'));
    }

    public function tearDown()
    {
        $this->routes = null;
    }
}

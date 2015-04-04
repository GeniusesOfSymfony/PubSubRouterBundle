<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Tests\PubSubTestCase;

class RouteCollectionTest extends PubSubTestCase
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
        $this->assertEquals($this->routes, $this->readProperty($routeCollection, 'routes'));
    }

    public function testClone()
    {
        $routeCollectionA = new RouteCollection($this->routes);
        $routeCollectionB = clone $routeCollectionA;

        $this->assertEquals(
            $this->readProperty($routeCollectionA, 'routes'),
            $this->readProperty($routeCollectionB, 'routes')
        );
    }

    public function testCount()
    {
        $routeCollection = new RouteCollection($this->routes);
        $this->assertCount(3, $this->readProperty($routeCollection, 'routes'));
    }

    public function testAdd()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('routeA', $this->routes['routeA']);

        $this->assertEquals(['routeA' => $this->routes['routeA']], $this->readProperty($routeCollection, 'routes'));

        $routeCollection->add('routeB', $this->routes['routeB']);

        $this->assertEquals([
            'routeA' => $this->routes['routeA'],
            'routeB' => $this->routes['routeB'],
        ], $this->readProperty($routeCollection, 'routes'));
    }

    public function testRemove()
    {
        $routeCollection = new RouteCollection($this->routes);

        $routeCollection->remove('routeA');

        $this->assertEquals([
            'routeB' => $this->routes['routeB'],
            'routeC' => $this->routes['routeC'],
        ], $this->readProperty($routeCollection, 'routes'));
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

        $this->assertEquals($this->routes, $this->readProperty($routeCollectionA, 'routes'));
    }

    public function tearDown()
    {
        $this->routes = null;
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;

class RouteCollectionTest extends TestCase
{
    public function testRoute()
    {
        $collection = new RouteCollection();
        $route = new Route('/foo', 'strlen');
        $collection->add('foo', $route);
        $this->assertEquals(['foo' => $route], $collection->all(), '->add() adds a route');
        $this->assertEquals($route, $collection->get('foo'), '->get() returns a route by name');
        $this->assertNull($collection->get('bar'), '->get() returns null if a route does not exist');
    }

    public function testOverriddenRoute()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo', 'strlen'));
        $collection->add('foo', new Route('/foo1', 'strlen'));

        $this->assertEquals('/foo1', $collection->get('foo')->getPattern());
    }

    public function testDeepOverriddenRoute()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo', 'strlen'));

        $collection1 = new RouteCollection();
        $collection1->add('foo', new Route('/foo1', 'strlen'));

        $collection2 = new RouteCollection();
        $collection2->add('foo', new Route('/foo2', 'strlen'));

        $collection1->addCollection($collection2);
        $collection->addCollection($collection1);

        $this->assertEquals('/foo2', $collection1->get('foo')->getPattern());
        $this->assertEquals('/foo2', $collection->get('foo')->getPattern());
    }

    public function testIterator()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo', 'strlen'));

        $collection1 = new RouteCollection();
        $collection1->add('bar', $bar = new Route('/bar', 'strlen'));
        $collection1->add('foo', $foo = new Route('/foo-new', 'strlen'));
        $collection->addCollection($collection1);
        $collection->add('last', $last = new Route('/last', 'strlen'));

        $this->assertInstanceOf(\ArrayIterator::class, $collection->getIterator());
        $this->assertSame(['bar' => $bar, 'foo' => $foo, 'last' => $last], $collection->getIterator()->getArrayCopy());
    }

    public function testCount()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo', 'strlen'));

        $collection1 = new RouteCollection();
        $collection1->add('bar', new Route('/bar', 'strlen'));
        $collection->addCollection($collection1);

        $this->assertCount(2, $collection);
    }

    public function testAddCollection()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo', 'strlen'));

        $collection1 = new RouteCollection();
        $collection1->add('bar', $bar = new Route('/bar', 'strlen'));
        $collection1->add('foo', $foo = new Route('/foo-new', 'strlen'));

        $collection2 = new RouteCollection();
        $collection2->add('grandchild', $grandchild = new Route('/grandchild', 'strlen'));

        $collection1->addCollection($collection2);
        $collection->addCollection($collection1);
        $collection->add('last', $last = new Route('/last', 'strlen'));

        $this->assertSame(
            ['bar' => $bar, 'foo' => $foo, 'grandchild' => $grandchild, 'last' => $last],
            $collection->all(),
            '->addCollection() imports routes of another collection, overrides if necessary and adds them at the end'
        );
    }

    public function testResource()
    {
        $collection = new RouteCollection();
        $collection->addResource($foo = new FileResource(__DIR__.'/../Fixtures/validchannel.yml'));
        $collection->addResource(new FileResource(__DIR__.'/../Fixtures/validchannel.yml'));

        $this->assertEquals(
            [$foo],
            $collection->getResources(),
            '->addResource() adds a resource and getResources() only returns unique ones by comparing the string representation'
        );
    }

    public function testGet()
    {
        $collection1 = new RouteCollection();
        $collection1->add('a', $a = new Route('/a', 'strlen'));
        $collection2 = new RouteCollection();
        $collection2->add('b', $b = new Route('/b', 'strlen'));
        $collection1->addCollection($collection2);
        $collection1->add('$péß^a|', $c = new Route('/special', 'strlen'));

        $this->assertSame($b, $collection1->get('b'), '->get() returns correct route in child collection');
        $this->assertSame($c, $collection1->get('$péß^a|'), '->get() can handle special characters');
        $this->assertNull($collection2->get('a'), '->get() does not return the route defined in parent collection');
        $this->assertNull($collection1->get('non-existent'), '->get() returns null when route does not exist');
        $this->assertNull($collection1->get(0), '->get() does not disclose internal child RouteCollection');
    }

    public function testRemove()
    {
        $collection = new RouteCollection();
        $collection->add('foo', $foo = new Route('/foo', 'strlen'));

        $collection1 = new RouteCollection();
        $collection1->add('bar', $bar = new Route('/bar', 'strlen'));
        $collection->addCollection($collection1);
        $collection->add('last', $last = new Route('/last', 'strlen'));

        $collection->remove('foo');
        $this->assertSame(['bar' => $bar, 'last' => $last], $collection->all(), '->remove() can remove a single route');
        $collection->remove(['bar', 'last']);
        $this->assertSame([], $collection->all(), '->remove() accepts an array and can remove multiple routes at once');
    }

    public function testClone()
    {
        $collection = new RouteCollection();
        $collection->add('a', new Route('/a', 'strlen'));
        $collection->add('b', new Route('/b', 'strlen'));

        $clonedCollection = clone $collection;

        $this->assertCount(2, $clonedCollection);
        $this->assertEquals($collection->get('a'), $clonedCollection->get('a'));
        $this->assertNotSame($collection->get('a'), $clonedCollection->get('a'));
        $this->assertEquals($collection->get('b'), $clonedCollection->get('b'));
        $this->assertNotSame($collection->get('b'), $clonedCollection->get('b'));
    }
}

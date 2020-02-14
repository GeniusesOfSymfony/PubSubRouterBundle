<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\ClosureLoader;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;

final class ClosureLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $loader = new ClosureLoader();

        $closure = function (): void {};

        $this->assertTrue($loader->supports($closure), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');

        $this->assertTrue($loader->supports($closure, 'closure'), '->supports() checks the resource type if specified');
        $this->assertFalse($loader->supports($closure, 'foo'), '->supports() checks the resource type if specified');
    }

    public function testLoad(): void
    {
        $loader = new ClosureLoader();

        $route = new Route('/', 'strlen');

        $routes = $loader->load(function () use ($route): RouteCollection {
            $routes = new RouteCollection();

            $routes->add('foo', $route);

            return $routes;
        });

        $this->assertEquals($route, $routes->get('foo'), '->load() loads a \Closure resource');
    }
}

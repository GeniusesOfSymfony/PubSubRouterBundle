<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\PhpFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;

class PhpFileLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $loader = new PhpFileLoader($this->createMock(FileLocator::class));

        $this->assertTrue($loader->supports('foo.php'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');

        $this->assertTrue($loader->supports('foo.php', 'php'), '->supports() checks the resource type if specified');
        $this->assertFalse($loader->supports('foo.php', 'foo'), '->supports() checks the resource type if specified');
    }

    public function testLoadWithRoute(): void
    {
        $loader = new PhpFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('validchannel.php');
        $routes = $routeCollection->all();

        $this->assertCount(1, $routes, 'One route is loaded');
        $this->assertContainsOnly(Route::class, $routes);

        foreach ($routes as $route) {
            $this->assertSame('chat/{user}', $route->getPattern());
            $this->assertSame('strlen', $route->getCallback());
        }
    }

    public function testLoadWithImport(): void
    {
        $loader = new PhpFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('validresource.php');
        $routes = $routeCollection->all();

        $this->assertCount(1, $routes, 'One route is loaded');
        $this->assertContainsOnly(Route::class, $routes);

        foreach ($routes as $route) {
            $this->assertSame('chat/{user}', $route->getPattern());
            $this->assertSame('strlen', $route->getCallback());
            $this->assertSame(123, $route->getDefault('user'), 'The default value for the user route variable should be overridden');
            $this->assertSame('\d+', $route->getRequirement('user'));
            $this->assertSame('car', $route->getOption('foo'), 'The default value for the foo option should be overridden');
        }
    }

    public function testRoutingConfigurator(): void
    {
        $loader = new PhpFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('php_dsl.php');
        $routes = $routeCollection->all();

        $this->assertCount(2, $routes, 'Two routes are loaded');
        $this->assertContainsOnly(Route::class, $routes);

        $expectedCollection = new RouteCollection();
        $expectedCollection->add(
            'user_chat',
            new Route(
                'chat/{user}',
                'strlen',
                [
                    'user' => 42,
                ],
                [
                    'user' => '\d+',
                ],
                [
                    'foo' => 'bar',
                ]
            )
        );

        $expectedCollection->add(
            'user_read',
            new Route(
                'users/{user}',
                'strlen',
                [
                    'user' => 42,
                ],
                [
                    'user' => '\d+',
                ],
                [
                    'foo' => 'bar',
                ]
            )
        );

        $expectedCollection->addResource(new FileResource(realpath(__DIR__.'/../Fixtures/php_dsl_import.php')));
        $expectedCollection->addResource(new FileResource(realpath(__DIR__.'/../Fixtures/php_dsl.php')));

        $this->assertEquals($expectedCollection, $routeCollection);
    }
}

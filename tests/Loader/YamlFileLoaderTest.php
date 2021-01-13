<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\YamlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCompiler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;

class YamlFileLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $loader = new YamlFileLoader($this->createMock(FileLocator::class));

        $this->assertTrue($loader->supports('foo.yml'), '->supports() returns true if the resource is loadable');
        $this->assertTrue($loader->supports('foo.yaml'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');

        $this->assertTrue($loader->supports('foo.yml', 'yaml'), '->supports() checks the resource type if specified');
        $this->assertTrue($loader->supports('foo.yaml', 'yaml'), '->supports() checks the resource type if specified');
        $this->assertFalse($loader->supports('foo.yml', 'foo'), '->supports() checks the resource type if specified');
    }

    public function testLoadDoesNothingIfEmpty(): void
    {
        $loader = new YamlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $collection = $loader->load('empty.yml');

        $this->assertEquals([], $collection->all());
        $this->assertEquals(
            [new FileResource(realpath(__DIR__.'/../Fixtures/empty.yml'))],
            $collection->getResources()
        );
    }

    public function testLoadThrowsExceptionWithInvalidFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $loader = new YamlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $loader->load('nonvalid.yml');
    }

    public function testLoadThrowsExceptionWithDuplicatedCallbackConfiguration(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $loader = new YamlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $loader->load('nonvalidcallbackandhandler.yml');
    }

    public function testLoadWithRoute(): void
    {
        $loader = new YamlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('validchannel.yml');
        $route = $routeCollection->get('user_chat');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('chat/{user}', $route->getPattern());
        $this->assertSame('strlen', $route->getCallback());
        $this->assertSame(['user' => 42], $route->getDefaults());
        $this->assertSame(['user' => '\\d+'], $route->getRequirements());
        $this->assertSame(['compiler_class' => RouteCompiler::class, 'foo' => 'bar'], $route->getOptions());
    }

    public function testLoadWithRouteWithDeprecatedProperties(): void
    {
        $loader = new YamlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('validchanneldeprecated.yml');
        $route = $routeCollection->get('user_chat');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('chat/{user}', $route->getPattern());
        $this->assertSame('strlen', $route->getCallback());
        $this->assertSame(['user' => 42], $route->getDefaults());
        $this->assertSame(['user' => '\\d+'], $route->getRequirements());
        $this->assertSame(['compiler_class' => RouteCompiler::class, 'foo' => 'bar'], $route->getOptions());
    }

    public function testLoadWithResource(): void
    {
        $loader = new YamlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('validresource.yml');
        $routes = $routeCollection->all();

        $this->assertCount(1, $routes, 'One route is loaded');
        $this->assertContainsOnly(Route::class, $routes);

        foreach ($routes as $route) {
            $this->assertSame(123, $route->getDefault('user'), 'The default value for the user route variable should be overridden');
            $this->assertSame('\d+', $route->getRequirement('user'));
            $this->assertSame('car', $route->getOption('foo'), 'The default value for the foo option should be overridden');
        }
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\XmlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCompiler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;

final class XmlFileLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $loader = new XmlFileLoader($this->createMock(FileLocator::class));

        $this->assertTrue($loader->supports('foo.xml'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');

        $this->assertTrue($loader->supports('foo.xml', 'xml'), '->supports() checks the resource type if specified');
        $this->assertFalse($loader->supports('foo.xml', 'foo'), '->supports() checks the resource type if specified');
    }

    public function testLoadWithRoute(): void
    {
        $loader = new XmlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('validchannel.xml');
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
        $loader = new XmlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('validchanneldeprecated.xml');
        $route = $routeCollection->get('user_chat');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('chat/{user}', $route->getPattern());
        $this->assertSame('strlen', $route->getCallback());
        $this->assertSame(['user' => 42], $route->getDefaults());
        $this->assertSame(['user' => '\\d+'], $route->getRequirements());
        $this->assertSame(['compiler_class' => RouteCompiler::class, 'foo' => 'bar'], $route->getOptions());
    }

    public function testLoadWithImport(): void
    {
        $loader = new XmlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('validresource.xml');
        $routes = $routeCollection->all();

        $this->assertCount(1, $routes, 'One route is loaded');
        $this->assertContainsOnly(Route::class, $routes);

        foreach ($routes as $route) {
            $this->assertSame(123, $route->getDefault('user'), 'The default value for the user route variable should be overridden');
            $this->assertSame('\d+', $route->getRequirement('user'));
            $this->assertSame('car', $route->getOption('foo'), 'The default value for the foo option should be overridden');
        }
    }

    /**
     * @dataProvider getPathsToInvalidFiles
     */
    public function testLoadThrowsExceptionWithInvalidFile(string $filePath): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $loader = new XmlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $loader->load($filePath);
    }

    public function getPathsToInvalidFiles(): array
    {
        return [['nonvalidnode.xml'], ['nonvalidroute.xml'], ['nonvalid.xml'], ['missing_id.xml'], ['missing_channel.xml']];
    }

    public function testScalarDataTypeDefaults(): void
    {
        $loader = new XmlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('scalar_defaults.xml');
        $route = $routeCollection->get('blog');

        $this->assertSame(
            [
                'slug' => null,
                'published' => true,
                'page' => 1,
                'price' => 3.5,
                'archived' => false,
                'free' => true,
                'locked' => false,
                'foo' => null,
                'bar' => null,
            ],
            $route->getDefaults()
        );
    }
}

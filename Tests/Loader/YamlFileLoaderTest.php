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
    public function testSupports()
    {
        $loader = new YamlFileLoader($this->getMockBuilder(FileLocator::class)->getMock());

        $this->assertTrue($loader->supports('foo.yml'), '->supports() returns true if the resource is loadable');
        $this->assertTrue($loader->supports('foo.yaml'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');

        $this->assertTrue($loader->supports('foo.yml', 'yaml'), '->supports() checks the resource type if specified');
        $this->assertTrue($loader->supports('foo.yaml', 'yaml'), '->supports() checks the resource type if specified');
        $this->assertFalse($loader->supports('foo.yml', 'foo'), '->supports() checks the resource type if specified');
    }

    public function testLoadDoesNothingIfEmpty()
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../Fixtures')));
        $collection = $loader->load('empty.yml');

        $this->assertEquals([], $collection->all());
        $this->assertEquals(
            [new FileResource(realpath(__DIR__.'/../Fixtures/empty.yml'))],
            $collection->getResources()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadThrowsExceptionWithInvalidFile()
    {
        $loader = new YamlFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $loader->load('nonvalid.yml');
    }

    public function testLoadWithRoute()
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
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Loader\YamlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Router\Router;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;

class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    private $router = null;

    /**
     * @var LoaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loader = null;

    protected function setUp(): void
    {
        $this->loader = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $this->router = new Router('test', $this->loader, ['routing.yml']);
    }

    public function testSetOptionsWithSupportedOptions()
    {
        $this->router->setOptions(
            [
                'cache_dir' => './cache',
                'debug' => true,
                'resource_type' => 'ResourceType',
            ]
        );

        $this->assertSame('./cache', $this->router->getOption('cache_dir'));
        $this->assertTrue($this->router->getOption('debug'));
        $this->assertSame('ResourceType', $this->router->getOption('resource_type'));
    }

    public function testSetOptionsWithUnsupportedOptions()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Router does not support the following options: "option_foo", "option_bar"');

        $this->router->setOptions(
            [
                'cache_dir' => './cache',
                'option_foo' => true,
                'option_bar' => 'baz',
                'resource_type' => 'ResourceType',
            ]
        );
    }

    public function testSetOptionWithSupportedOption()
    {
        $this->router->setOption('cache_dir', './cache');

        $this->assertSame('./cache', $this->router->getOption('cache_dir'));
    }

    public function testSetOptionWithUnsupportedOption()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Router does not support the "option_foo" option');

        $this->router->setOption('option_foo', true);
    }

    public function testGetOptionWithUnsupportedOption()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Router does not support the "option_foo" option');

        $this->router->getOption('option_foo');
    }

    public function testThatRouteCollectionIsLoaded()
    {
        $this->router->setOption('resource_type', 'ResourceType');

        $routeCollection = new RouteCollection();

        $this->loader->expects($this->once())
            ->method('load')
            ->with('routing.yml', 'ResourceType')
            ->willReturn($routeCollection);

        $this->assertSame($routeCollection, $this->router->getCollection());
    }

    /**
     * @dataProvider provideMatcherOptionsPreventingCaching
     */
    public function testMatcherIsCreatedIfCacheIsNotConfigured($option)
    {
        $this->router->setOption($option, null);

        $this->loader->expects($this->once())
            ->method('load')
            ->with('routing.yml', null)
            ->willReturn(new RouteCollection());

        $this->assertInstanceOf(Matcher::class, $this->router->getMatcher());
    }

    public function provideMatcherOptionsPreventingCaching()
    {
        return [
            ['cache_dir'],
            ['matcher_cache_class'],
        ];
    }

    /**
     * @dataProvider provideGeneratorOptionsPreventingCaching
     */
    public function testGeneratorIsCreatedIfCacheIsNotConfigured($option)
    {
        $this->router->setOption($option, null);

        $this->loader->expects($this->once())
            ->method('load')
            ->with('routing.yml', null)
            ->willReturn(new RouteCollection());

        $this->assertInstanceOf(Generator::class, $this->router->getGenerator());
    }

    public function provideGeneratorOptionsPreventingCaching()
    {
        return [
            ['cache_dir'],
            ['generator_cache_class'],
        ];
    }

    public function testResourcesAreLoadedToCollection()
    {
        $router = new Router(
            'test',
            new YamlFileLoader(new FileLocator([__DIR__.'/../Fixtures'])),
            [
                'empty.yml',
                'validchannel.yml',
            ]
        );

        $collection = $router->getCollection();

        $this->assertCount(2, $collection->getResources(), 'The loader should process all resources');
        $this->assertCount(1, $collection, 'The loader should register all routes');
    }
}

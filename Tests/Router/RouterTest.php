<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Router\Router;
use PHPUnit\Framework\TestCase;
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

    protected function setUp()
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Router does not support the following options: "option_foo", "option_bar"
     */
    public function testSetOptionsWithUnsupportedOptions()
    {
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Router does not support the "option_foo" option
     */
    public function testSetOptionWithUnsupportedOption()
    {
        $this->router->setOption('option_foo', true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Router does not support the "option_foo" option
     */
    public function testGetOptionWithUnsupportedOption()
    {
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
        return array(
            array('cache_dir'),
            array('matcher_cache_class'),
        );
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
        return array(
            array('cache_dir'),
            array('generator_cache_class'),
        );
    }
}

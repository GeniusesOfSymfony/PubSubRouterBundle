<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Loader\ClosureLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\ContainerLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\GlobFileLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\PhpFileLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\XmlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Loader\YamlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Router\Router;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Container;

final class RouterTest extends TestCase
{
    private ?Router $router = null;

    /**
     * @var MockObject&LoaderInterface
     */
    private $loader;

    protected function setUp(): void
    {
        $this->loader = $this->createMock(LoaderInterface::class);
        $this->router = new Router('test', $this->loader, ['routing.yml']);
    }

    public function testSetOptionsWithSupportedOptions(): void
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

    public function testSetOptionsWithUnsupportedOptions(): void
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

    public function testSetOptionWithSupportedOption(): void
    {
        $this->router->setOption('cache_dir', './cache');

        $this->assertSame('./cache', $this->router->getOption('cache_dir'));
    }

    public function testSetOptionWithUnsupportedOption(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Router does not support the "option_foo" option');

        $this->router->setOption('option_foo', true);
    }

    public function testGetOptionWithUnsupportedOption(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Router does not support the "option_foo" option');

        $this->router->getOption('option_foo');
    }

    public function testThatRouteCollectionIsLoaded(): void
    {
        $this->router->setOption('resource_type', 'ResourceType');

        $this->loader->expects($this->once())
            ->method('load')
            ->with('routing.yml', 'ResourceType')
            ->willReturn(new RouteCollection());

        $this->assertInstanceOf(RouteCollection::class, $this->router->getCollection());
    }

    public function testThatRouteCollectionIsLoadedWhenARouterHasNoResources(): void
    {
        $router = new Router('test', $this->loader, []);
        $router->setOption('resource_type', 'ResourceType');

        $this->loader->expects($this->never())
            ->method('load');

        $this->assertInstanceOf(RouteCollection::class, $router->getCollection());
    }

    public function testThatRouteCollectionIsLoadedWithMixedResourceTypes(): void
    {
        $container = new Container();
        $container->set('routing.loader', new class() {
            public function __invoke(ContainerLoader $loader): RouteCollection
            {
                $collection = new RouteCollection();
                $collection->add(
                    'my_chat',
                    new Route(
                        'chat/{my}',
                        'strlen',
                        [
                            'my' => 42,
                        ],
                        [
                            'my' => '\d+',
                        ],
                        [
                            'foo' => 'bar',
                        ]
                    )
                );

                return $collection;
            }
        });

        $locator = new FileLocator([__DIR__.'/../Fixtures']);

        $resolver = new LoaderResolver();
        $resolver->addLoader(new ClosureLoader());
        $resolver->addLoader(new ContainerLoader($container));
        $resolver->addLoader(new GlobFileLoader($locator));
        $resolver->addLoader(new PhpFileLoader($locator));
        $resolver->addLoader(new XmlFileLoader($locator));
        $resolver->addLoader(new YamlFileLoader($locator));

        $router = new Router(
            'test',
            new DelegatingLoader($resolver),
            [
                'validresource.yml',
                'validresource.xml',
                'validresource.php',
                [
                    'resource' => 'routing.loader',
                    'type' => 'service',
                ],
                [
                    'resource' => __DIR__.'/../Fixtures/directory/*.yml',
                    'type' => 'glob',
                ],
                [
                    'resource' => static function (): RouteCollection {
                        $collection = new RouteCollection();
                        $collection->add(
                            'anon_chat',
                            new Route(
                                'anonymous-chat',
                                'strlen'
                            )
                        );

                        return $collection;
                    },
                    'type' => 'closure',
                ],
            ]
        );

        $this->assertInstanceOf(RouteCollection::class, $router->getCollection());
        $this->assertNotCount(0, $router->getCollection());
    }

    /**
     * @dataProvider provideMatcherOptionsPreventingCaching
     */
    public function testMatcherIsCreatedIfCacheIsNotConfigured(string $option): void
    {
        $this->router->setOption($option, null);

        $this->loader->expects($this->once())
            ->method('load')
            ->with('routing.yml', null)
            ->willReturn(new RouteCollection());

        $this->assertInstanceOf(Matcher::class, $this->router->getMatcher());
    }

    public function provideMatcherOptionsPreventingCaching(): \Generator
    {
        yield ['cache_dir'];
    }

    /**
     * @dataProvider provideGeneratorOptionsPreventingCaching
     */
    public function testGeneratorIsCreatedIfCacheIsNotConfigured(string $option): void
    {
        $this->router->setOption($option, null);

        $this->loader->expects($this->once())
            ->method('load')
            ->with('routing.yml', null)
            ->willReturn(new RouteCollection());

        $this->assertInstanceOf(Generator::class, $this->router->getGenerator());
    }

    public function provideGeneratorOptionsPreventingCaching(): \Generator
    {
        yield ['cache_dir'];
    }

    public function testResourcesAreLoadedToCollection(): void
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

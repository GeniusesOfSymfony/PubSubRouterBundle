<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\CacheWarmer;

use Gos\Bundle\PubSubRouterBundle\CacheWarmer\RouterCacheWarmer;
use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Router\RouterInterface;
use Gos\Bundle\PubSubRouterBundle\Router\RouterRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

final class RouterCacheWarmerTest extends TestCase
{
    public function testWarmUpWithWarmableInterface(): void
    {
        /** @var MockObject&testRouterInterfaceWithWarmableInterface $router */
        $router = $this->createMock(testRouterInterfaceWithWarmableInterface::class);

        $router->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('router1');

        $router->expects($this->once())
            ->method('warmUp')
            ->with('/tmp')
            ->willReturn(
                [
                    Generator::class,
                    Matcher::class,
                ]
            );

        /** @var MockObject&testRouterInterfaceWithWarmableInterface $router2 */
        $router2 = $this->createMock(testRouterInterfaceWithWarmableInterface::class);

        $router2->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('router2');

        $router2->expects($this->once())
            ->method('warmUp')
            ->with('/tmp')
            ->willReturn(
                [
                    Generator::class,
                    Matcher::class,
                ]
            );

        $routerRegistry = new RouterRegistry();
        $routerRegistry->addRouter($router);
        $routerRegistry->addRouter($router2);

        /** @var MockObject&ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->with('gos_pubsub_router.router_registry')
            ->willReturn($routerRegistry);

        $this->assertSame(
            [
                Generator::class,
                Matcher::class,
            ],
            (new RouterCacheWarmer($container))->warmUp('/tmp')
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testWarmUpWithoutWarmableInterface(): void
    {
        /** @var MockObject&testRouterInterfaceWithoutWarmableInterface $router */
        $router = $this->createMock(testRouterInterfaceWithoutWarmableInterface::class);

        $routerRegistry = new RouterRegistry();
        $routerRegistry->addRouter($router);

        /** @var MockObject&ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->with('gos_pubsub_router.router_registry')
            ->willReturn($routerRegistry);

        (new RouterCacheWarmer($container))->warmUp('/tmp');
    }
}

interface testRouterInterfaceWithWarmableInterface extends RouterInterface, WarmableInterface
{
}

interface testRouterInterfaceWithoutWarmableInterface extends RouterInterface
{
}

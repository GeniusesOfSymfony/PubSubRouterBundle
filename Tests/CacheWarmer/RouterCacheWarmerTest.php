<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\CacheWarmer;

use Gos\Bundle\PubSubRouterBundle\CacheWarmer\RouterCacheWarmer;
use Gos\Bundle\PubSubRouterBundle\Router\RouterInterface;
use Gos\Bundle\PubSubRouterBundle\Router\RouterRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

class RouterCacheWarmerTest extends TestCase
{
    public function testWarmUpWithWarmableInterface()
    {
        $router = $this->getMockBuilder(testRouterInterfaceWithWarmableInterface::class)
            ->setMethods(['match', 'generate', 'getCollection', 'getName', 'warmUp'])
            ->getMock();

        $router->expects($this->any())
            ->method('warmUp')
            ->with('/tmp')
            ->willReturn('');

        $routerRegistry = new RouterRegistry();
        $routerRegistry->addRouter($router);

        $container = $this->getMockBuilder(ContainerInterface::class)->setMethods(['get', 'has'])->getMock();
        $container->expects($this->any())
            ->method('get')
            ->with('gos_pubsub_router.router.registry')
            ->willReturn($routerRegistry);

        (new RouterCacheWarmer($container))->warmUp('/tmp');

        $this->addToAssertionCount(1);
    }

    public function testWarmUpWithoutWarmableInterface()
    {
        $router = $this->getMockBuilder(testRouterInterfaceWithoutWarmableInterface::class)
            ->setMethods(['match', 'generate', 'getCollection', 'getName'])
            ->getMock();

        $routerRegistry = new RouterRegistry();
        $routerRegistry->addRouter($router);

        $container = $this->getMockBuilder(ContainerInterface::class)->setMethods(['get', 'has'])->getMock();
        $container->expects($this->any())
            ->method('get')
            ->with('gos_pubsub_router.router.registry')
            ->willReturn($routerRegistry);

        (new RouterCacheWarmer($container))->warmUp('/tmp');

        $this->addToAssertionCount(1);
    }
}

interface testRouterInterfaceWithWarmableInterface extends RouterInterface, WarmableInterface
{
}

interface testRouterInterfaceWithoutWarmableInterface extends RouterInterface
{
}

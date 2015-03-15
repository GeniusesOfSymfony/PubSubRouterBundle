<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Loader\YamlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Router\Router;
use Gos\Bundle\PubSubRouterBundle\Router\RouterContext;
use Prophecy\Argument;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testAddLoader()
    {
        $loader = $this->getMock(LoaderInterface::CLASS);
        $router = new Router();
        $router->addLoader($loader);
        $this->assertEquals(
            [$loader],
            \PHPUnit_Framework_Assert::readAttribute($router, 'loaders')
        );
    }

    public function testSetContext()
    {
        $router = new Router();
        $router->setContext(new RouterContext());

        $this->assertInstanceOf(RouterContext::CLASS, \PHPUnit_Framework_Assert::readAttribute($router, 'context'));
    }

    public function testGetContext()
    {
        $router = new Router();
        $router->setContext(new RouterContext());

        $this->assertInstanceOf(RouterContext::class, $router->getContext());
    }

    public function testAddResource()
    {
        $resource = 'somthing';
        $router = new Router();
        $router->addResource($resource);
        $this->assertEquals(
            [$resource],
            \PHPUnit_Framework_Assert::readAttribute($router, 'resources')
        );
    }

    public function testGetCollectionWhenRouterIsNotLoaded()
    {
        $router = new Router();
        $this->assertInstanceOf(RouteCollection::CLASS, $router->getCollection());
    }

    public function testIsLoadedWithoutResource()
    {
        $router = new Router();
        $this->assertFalse(\PHPUnit_Framework_Assert::readAttribute($router, 'loaded'));
        $router->addLoader($this->getMock(LoaderInterface::CLASS));
        $router->loadRoute();
        $this->assertTrue(\PHPUnit_Framework_Assert::readAttribute($router, 'loaded'));
    }

    public function testLoadRoute()
    {
        $router = new Router;
        $router->addResource('@resource/collectionA.yml');
        $router->addResource('@resource/collectionB.yml');
        $router->addResource('@resource/collectionC.yml');

        $routeA = new Route('channel/*', ['redis']);
        $routeCollectionA = new RouteCollection();
        $routeCollectionA->add('routeA', $routeA);

        $routeB = new Route('channel/abc', ['websocket']);
        $routeCollectionB = new RouteCollection();
        $routeCollectionB->add('routeB', $routeB);

        $routeCollectionC = new RouteCollection([
            'routeC' => $routeC = new Route('channel/123', [ 'mongodb' ]),
            'routeD' => $routeD = new Route('channel/AZERTY', [ 'redis' ])
        ]);

        $loader = $this->prophesize(YamlFileLoader::CLASS);

        $loader
            ->setResolver(Argument::type(LoaderResolverInterface::CLASS))
            ->willReturn($this->prophesize(LoaderResolverInterface::CLASS)->reveal())
        ;

        $loader->supports(Argument::type('string'), null)->willReturn(true);

        $loader->load('@resource/collectionA.yml', null)->willReturn($routeCollectionA);
        $loader->load('@resource/collectionB.yml', null)->willReturn($routeCollectionB);
        $loader->load('@resource/collectionC.yml', null)->willReturn($routeCollectionC);

        $router->addLoader($loader->reveal());

        $router->loadRoute();

        $this->assertEquals(new RouteCollection([
            'routeA' => $routeA,
            'routeB' => $routeB,
            'routeC' => $routeC,
            'routeD' => $routeD
        ]), $router->getCollection());
    }
}
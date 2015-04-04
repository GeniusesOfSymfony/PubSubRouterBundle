<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoader;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Router\Router;
use Gos\Bundle\PubSubRouterBundle\Router\RouterContext;
use Gos\Bundle\PubSubRouterBundle\Tests\PubSubTestCase;

class RouterTest extends PubSubTestCase
{
    public function testMatchWithoutContext()
    {
        //without context
        $routeCollection = $this->prophesize(RouteCollection::CLASS);
        $matcher = $this->prophesize(Matcher::CLASS);
        $routeLoader = $this->prophesize(RouteLoader::CLASS);
        $generator = $this->prophesize(Generator::CLASS);
        $matcher->match('foo', '/')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());
        $router->match('foo', '/');

        //with context
        $matcher = $this->prophesize(Matcher::CLASS);
        $matcher->match('foo', '/')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(RouterContext::CLASS);
        $context->getTokenSeparator()->willReturn('/');
        $router->setContext($context->reveal());
        $router->match('foo');

        //with context overriding
        $matcher = $this->prophesize(Matcher::CLASS);
        $matcher->match('foo', ':')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(RouterContext::CLASS);
        $context->getTokenSeparator()->willReturn('/');
        $router->setContext($context->reveal());
        $router->match('foo', ':');
    }

    public function testGenerateFromTokens()
    {
        //without context
        $tokens = ['foo', 'bar', 'baz'];
        $parameters = ['baz' => 'foo'];
        $routeCollection = $this->prophesize(RouteCollection::CLASS);
        $matcher = $this->prophesize(Matcher::CLASS);
        $routeLoader = $this->prophesize(RouteLoader::CLASS);
        $generator = $this->prophesize(Generator::CLASS);
        $generator->generateFromTokens($tokens, $parameters, ':')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $router->generateFromTokens($tokens, $parameters, ':');

        //with context
        $generator = $this->prophesize(Generator::CLASS);
        $generator->generateFromTokens($tokens, $parameters, ':')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(RouterContext::CLASS);
        $context->getTokenSeparator()->willReturn(':');
        $router->setContext($context->reveal());

        $router->generateFromTokens($tokens, $parameters, null);

        //with context override
        $generator = $this->prophesize(Generator::CLASS);
        $generator->generateFromTokens($tokens, $parameters, '/')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(RouterContext::CLASS);
        $context->getTokenSeparator()->willReturn(':');
        $router->setContext($context->reveal());

        $router->generateFromTokens($tokens, $parameters, '/');
    }

    public function testGenerateWithoutContext()
    {
        //without context
        $routeCollection = $this->prophesize(RouteCollection::CLASS);
        $matcher = $this->prophesize(Matcher::CLASS);
        $routeLoader = $this->prophesize(RouteLoader::CLASS);
        $generator = $this->prophesize(Generator::CLASS);
        $generator->generate('foo', ['foo' => 'bar'], '/')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $router->generate('foo', ['foo' => 'bar'], '/');

        //with context
        $generator = $this->prophesize(Generator::CLASS);
        $generator->generate('foo', ['foo' => 'bar'], ':')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(RouterContext::CLASS);
        $context->getTokenSeparator()->willReturn(':');
        $router->setContext($context->reveal());
        $router->generate('foo', ['foo' => 'bar'], null);

        //with context override
        $generator = $this->prophesize(Generator::CLASS);
        $generator->generate('foo', ['foo' => 'bar'], '/')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(RouterContext::CLASS);
        $context->getTokenSeparator()->willReturn(':');
        $router->setContext($context->reveal());
        $router->generate('foo', ['foo' => 'bar'], '/');
    }

    public function testGetCollection()
    {
        $routeCollection = $this->prophesize(RouteCollection::CLASS);
        $matcher = $this->prophesize(Matcher::CLASS);
        $routeLoader = $this->prophesize(RouteLoader::CLASS);
        $generator = $this->prophesize(Generator::CLASS);

        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $this->assertInstanceOf(RouteCollection::CLASS, $router->getCollection());
    }

    public function testSetContext()
    {
        $routeCollection = $this->prophesize(RouteCollection::CLASS);
        $matcher = $this->prophesize(Matcher::CLASS);
        $generator = $this->prophesize(Generator::CLASS);
        $routeLoader = $this->prophesize(RouteLoader::CLASS);

        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $router->setContext(new RouterContext());
        $this->assertInstanceOf(RouterContext::CLASS, $this->readProperty($router, 'context'));
    }

    public function testGetContext()
    {
        $routeCollection = $this->prophesize(RouteCollection::CLASS);
        $matcher = $this->prophesize(Matcher::CLASS);
        $generator = $this->prophesize(Generator::CLASS);
        $routeLoader = $this->prophesize(RouteLoader::CLASS);

        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $router->setContext(new RouterContext());
        $this->assertInstanceOf(RouterContext::class, $router->getContext());
    }
}

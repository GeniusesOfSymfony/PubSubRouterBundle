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
        $routeCollection = $this->prophesize(get_class(RouteCollection));
        $matcher = $this->prophesize(get_class(Matcher));
        $routeLoader = $this->prophesize(get_class(RouteLoader));
        $generator = $this->prophesize(get_class(Generator));
        $matcher->match('foo', '/')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());
        $router->match('foo', '/');

        //with context
        $matcher = $this->prophesize(get_class(Matcher));
        $matcher->match('foo', '/')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(get_class(RouterContext));
        $context->getTokenSeparator()->willReturn('/');
        $router->setContext($context->reveal());
        $router->match('foo');

        //with context overriding
        $matcher = $this->prophesize(get_class(Matcher));
        $matcher->match('foo', ':')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(get_class(RouterContext));
        $context->getTokenSeparator()->willReturn('/');
        $router->setContext($context->reveal());
        $router->match('foo', ':');
    }

    public function testGenerateFromTokens()
    {
        //without context
        $tokens = ['foo', 'bar', 'baz'];
        $parameters = ['baz' => 'foo'];
        $routeCollection = $this->prophesize(get_class(RouteCollection));
        $matcher = $this->prophesize(get_class(Matcher));
        $routeLoader = $this->prophesize(get_class(RouteLoader));
        $generator = $this->prophesize(get_class(Generator));
        $generator->generateFromTokens($tokens, $parameters, ':')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $router->generateFromTokens($tokens, $parameters, ':');

        //with context
        $generator = $this->prophesize(get_class(Generator));
        $generator->generateFromTokens($tokens, $parameters, ':')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(get_class(RouterContext));
        $context->getTokenSeparator()->willReturn(':');
        $router->setContext($context->reveal());

        $router->generateFromTokens($tokens, $parameters, null);

        //with context override
        $generator = $this->prophesize(get_class(Generator));
        $generator->generateFromTokens($tokens, $parameters, '/')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(get_class(RouterContext));
        $context->getTokenSeparator()->willReturn(':');
        $router->setContext($context->reveal());

        $router->generateFromTokens($tokens, $parameters, '/');
    }

    public function testGenerateWithoutContext()
    {
        //without context
        $routeCollection = $this->prophesize(get_class(RouteCollection));
        $matcher = $this->prophesize(get_class(Matcher));
        $routeLoader = $this->prophesize(get_class(RouteLoader));
        $generator = $this->prophesize(get_class(Generator));
        $generator->generate('foo', ['foo' => 'bar'], '/')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $router->generate('foo', ['foo' => 'bar'], '/');

        //with context
        $generator = $this->prophesize(get_class(Generator));
        $generator->generate('foo', ['foo' => 'bar'], ':')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(get_class(RouterContext));
        $context->getTokenSeparator()->willReturn(':');
        $router->setContext($context->reveal());
        $router->generate('foo', ['foo' => 'bar'], null);

        //with context override
        $generator = $this->prophesize(get_class(Generator));
        $generator->generate('foo', ['foo' => 'bar'], '/')->shouldBeCalled();
        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $context = $this->prophesize(get_class(RouterContext));
        $context->getTokenSeparator()->willReturn(':');
        $router->setContext($context->reveal());
        $router->generate('foo', ['foo' => 'bar'], '/');
    }

    public function testGetCollection()
    {
        $routeCollection = $this->prophesize(get_class(RouteCollection));
        $matcher = $this->prophesize(get_class(Matcher));
        $routeLoader = $this->prophesize(get_class(RouteLoader));
        $generator = $this->prophesize(get_class(Generator));

        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $this->assertInstanceOf(get_class(RouteCollection), $router->getCollection());
    }

    public function testSetContext()
    {
        $routeCollection = $this->prophesize(get_class(RouteCollection));
        $matcher = $this->prophesize(get_class(Matcher));
        $generator = $this->prophesize(get_class(Generator));
        $routeLoader = $this->prophesize(get_class(RouteLoader));

        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $router->setContext(new RouterContext());
        $this->assertInstanceOf(get_class(RouterContext), $this->readProperty($router, 'context'));
    }

    public function testGetContext()
    {
        $routeCollection = $this->prophesize(get_class(RouteCollection));
        $matcher = $this->prophesize(get_class(Matcher));
        $generator = $this->prophesize(get_class(Generator));
        $routeLoader = $this->prophesize(get_class(RouteLoader));

        $router = new Router($routeCollection->reveal(), $matcher->reveal(), $generator->reveal(), $routeLoader->reveal());

        $router->setContext(new RouterContext());
        $this->assertInstanceOf(get_class(RouterContext), $router->getContext());
    }
}

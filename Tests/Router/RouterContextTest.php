<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Router\RouterContext;
use Gos\Bundle\PubSubRouterBundle\Tests\PubSubTestCase;

class RouterContextTest extends PubSubTestCase
{
    public function testGetTokenSeparator()
    {
        $context = new RouterContext();
        $this->setPropertyValue($context, 'tokenSeparator', '/');

        $this->assertEquals('/', $context->getTokenSeparator());
    }

    public function testSetTokenSeparator()
    {
        $context = new RouterContext();
        $context->setTokenSeparator('/');
        $this->assertEquals('/', $this->readProperty($context, 'tokenSeparator'));
    }
}

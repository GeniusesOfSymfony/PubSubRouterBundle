<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Router\RouterContext;

class RouterContextTest extends \PHPUnit_Framework_TestCase
{
    protected function injectPropertyValue($obj, $property, $value)
    {
        $reflection = new \ReflectionClass(RouterContext::CLASS);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($obj, $value);
    }

    public function testGetTokenSeparator()
    {
        $context = new RouterContext();
        $this->injectPropertyValue($context, 'tokenSeparator', '/');

        $this->assertEquals('/', $context->getTokenSeparator());
    }

    public function testSetTokenSeparator()
    {
        $context = new RouterContext();
        $context->setTokenSeparator('/');
        $this->assertEquals('/', \PHPUnit_Framework_Assert::readAttribute($context, 'tokenSeparator'));
    }
}

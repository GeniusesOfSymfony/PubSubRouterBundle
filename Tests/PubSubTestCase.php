<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Tokenizer\Token;

abstract class PubSubTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $expression
     * @param bool   $isParameter
     * @param array  $requirements
     *
     * @return object
     */
    protected function createToken($expression, $isParameter = false, $requirements = [])
    {
        $token = $this->prophesize(Token::CLASS);
        $token->isParameter()->willReturn($isParameter);
        $token->getExpression()->willReturn($expression);
        $token->getRequirements()->willReturn($requirements);

        return $token->reveal();
    }

    /**
     * @param string $pattern
     * @param string $name
     * @param array  $requirements
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function createRoute($pattern, $name, $requirements = [])
    {
        $route = $this->prophesize(Route::CLASS);
        $route->getPattern()->willReturn($pattern);
        $route->getRequirements()->willReturn($requirements);
        $route->__toString()->willReturn($name);

        return $route;
    }

    protected function setPropertyValue($object, $propertyName, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getProperty($propertyName);
        $method->setAccessible(true);

        $method->setValue($object, $value);
    }

    public function invokeMethod($object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @param $object
     * @param $propertyName
     *
     * @return mixed
     */
    protected function readProperty($object, $propertyName)
    {
        return \PHPUnit_Framework_Assert::readAttribute($object, $propertyName);
    }
}

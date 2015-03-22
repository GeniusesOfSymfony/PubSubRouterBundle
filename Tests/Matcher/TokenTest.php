<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Matcher;

use Gos\Bundle\PubSubRouterBundle\Matcher\Token;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    public function setPropertyValue(&$object, $propertyName, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getProperty($propertyName);
        $method->setAccessible(true);

        $method->setValue($object, $value);
    }

    public function testInit()
    {
        $token = new Token();

        $this->assertEquals([], \PHPUnit_Framework_Assert::readAttribute($token, 'requirements'));
        $this->assertFalse(\PHPUnit_Framework_Assert::readAttribute($token, 'isParameter'));
    }

    public function testSetParameter()
    {
        $token = new Token();
        $token->setParameter();

        $this->assertTrue(\PHPUnit_Framework_Assert::readAttribute($token, 'isParameter'));
    }

    public function testIsParameter()
    {
        $token = new Token();

        $this->setPropertyValue($token, 'isParameter', true);
        $this->assertTrue($token->isParameter());
    }

    public function testGetExpression()
    {
        $token = new Token();

        $this->setPropertyValue($token, 'expression', 'foo');
        $this->assertEquals('foo', $token->getExpression());
    }

    public function testSetExpression()
    {
        $token = new Token();

        $token->setExpression('foo');
        $this->assertEquals('foo', \PHPUnit_Framework_Assert::readAttribute($token, 'expression'));
    }

    public function testSetRequirements()
    {
        $token = new Token();

        $token->setRequirements([
            'foo' => 'bar',
        ]);

        $this->assertEquals(['foo' => 'bar'], \PHPUnit_Framework_Assert::readAttribute($token, 'requirements'));
    }

    public function testGetRequirements()
    {
        $token = new Token();

        $this->setPropertyValue($token, 'requirements', ['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $token->getRequirements());
    }
}

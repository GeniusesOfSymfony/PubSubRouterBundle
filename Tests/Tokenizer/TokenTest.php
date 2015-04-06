<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Matcher;

use Gos\Bundle\PubSubRouterBundle\Tests\PubSubTestCase;
use Gos\Bundle\PubSubRouterBundle\Tokenizer\Token;

class TokenTest extends PubSubTestCase
{
    public function testInit()
    {
        $token = new Token();

        $this->assertEquals([], $this->readProperty($token, 'requirements'));
        $this->assertFalse($this->readProperty($token, 'isParameter'));
    }

    public function testSetParameter()
    {
        $token = new Token();
        $token->setParameter();

        $this->assertTrue($this->readProperty($token, 'isParameter'));
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
        $this->assertEquals('foo', $this->readProperty($token, 'expression'));
    }

    public function testSetRequirements()
    {
        $token = new Token();

        $token->setRequirements([
            'foo' => 'bar',
        ]);

        $this->assertEquals(['foo' => 'bar'], $this->readProperty($token, 'requirements'));
    }

    public function testGetRequirements()
    {
        $token = new Token();

        $this->setPropertyValue($token, 'requirements', ['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $token->getRequirements());
    }
}

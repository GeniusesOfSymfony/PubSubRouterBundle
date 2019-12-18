<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Router\CompiledRoute;
use PHPUnit\Framework\TestCase;

class CompiledRouteTest extends TestCase
{
    public function testAccessors(): void
    {
        $compiled = new CompiledRoute('prefix', 'regex', ['tokens'], ['variables']);
        $this->assertEquals(
            'prefix',
            $compiled->getStaticPrefix(),
            '__construct() takes a static prefix as its first argument'
        );
        $this->assertEquals('regex', $compiled->getRegex(), '__construct() takes a regexp as its second argument');
        $this->assertEquals(
            ['tokens'],
            $compiled->getTokens(),
            '__construct() takes an array of tokens as its third argument'
        );
        $this->assertEquals(
            ['variables'],
            $compiled->getVariables(),
            '__construct() takes an array of variables as its fourth argument'
        );
    }
}

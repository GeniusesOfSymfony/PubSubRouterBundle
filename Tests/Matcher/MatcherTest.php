<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Matcher;

use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;

class MatcherTest extends \PHPUnit_Framework_TestCase
{
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testMatch()
    {
        $matcher = new Matcher();
        $matcher->match('channel/user/foo-bar', '/');
    }

    /**
     * @dataProvider tokenProvider
     *
     * @param $channel
     * @param $separator
     * @param $expected
     */
    public function testTokenize($channel, $separator, $expected, $eq = true)
    {
        $matcher = new Matcher();

        $result = $this->invokeMethod($matcher, 'tokenize', [$channel, $separator]);

        if (true === $eq) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertNotEquals($expected, $result);
        }
    }

    public function tokenProvider()
    {
        return [
            ['channel/user/foo-bar', '/', ['channel', 'user', 'foo-bar']],
            ['channel:user:all', ':', ['channel', 'user', 'all']],
            ['channel:user:*', ':', ['channel', 'user', '*']],
        ];
    }
}

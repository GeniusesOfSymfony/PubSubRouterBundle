<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\InvalidArgumentException;
use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Matcher\Token;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

class MatcherTest extends \PHPUnit_Framework_TestCase
{
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    protected function getRouteCollection()
    {
        return new RouteCollection([
            'user_notification' => new Route(
                'notification/user/{uid}',
                ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'],
                ['gos_redis', 'gos_websocket'],
                [
                    'uid' => ['pattern' => "\d+", 'wildcard' => true],
                ]
            ),
            'application_notification' => new Route(
                'notification/application/{name}',
                ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'],
                ['gos_redis', 'gos_websocket'],
                [
                    'name' => ['pattern' => '[a-zA-Z0-9]', 'wildcard' => true],
                ]
            ),
            'application_chat_topic' => new Route(
                'application/chat/{room}',
                ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'],
                ['gos_redis', 'gos_websocket'],
                [
                    'room' => ['pattern' => '[a-zA-Z0-9]', 'wildcard' => true],
                ]
            ),
            'user_employee_role_notification' => new Route(
                'notification/user/{role}/{uid}',
                ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'],
                ['gos_redis', 'gos_websocket'],
                [
                    'role' => ['pattern' => 'admin|employee|user|anon'],
                    'uid' => ['pattern' => "\d+"],
                ]
            ),
            'simple_user' => new Route(
                'channel/user/foo-bar',
                ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'],
                ['gos_redis', 'gos_websocket']
            ),
        ]);
    }

    public function testMissMatchParameter()
    {
        $this->setExpectedException(InvalidArgumentException::CLASS);
        $route = new Route(
            'application/chat/{room}',
            ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'],
            ['gos_redis', 'gos_websocket'],
            [
                'name' => ['pattern' => '[a-zA-Z0-9]', 'wildcard' => true],
            ]
        );

        $matcher = new Matcher();

        $matcher->compare($route, 'application/chat/foobar', '/');
    }

    /**
     * @dataProvider compareProvider
     */
    public function testCompare(Route $route, $expression, $tokenSeparator, $expected)
    {
        $matcher = new Matcher();

        $result = $matcher->compare($route, $expression, $tokenSeparator);

        $this->assertEquals($result, $expected);
    }

    public function compareProvider()
    {
        $rc = $this->getRouteCollection();

        return [
            [$rc->get('user_notification'), 'notification/user/18', '/', true],
            [$rc->get('application_notification'), 'notification/application/*', '/', true],
            [$rc->get('application_notification'), 'notification/application/all', '/', true],
            [$rc->get('application_notification'), 'notification/application/@foo', '/', false],
            [$rc->get('user_notification'), 'notification/user/username', '/', false],
            [$rc->get('user_employee_role_notification'), 'notification/user/admin/1', '/', true],
            [$rc->get('user_employee_role_notification'), 'notification/user/*/azerty', '/', false],
            [$rc->get('user_employee_role_notification'), 'notification/user/admin/all', '/', false],
            [$rc->get('application_chat_topic'), 'application/chat/foobar', '/', true],
        ];
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
        $result = $matcher->tokenize($channel, $separator);

        if (true === $eq) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertNotEquals($expected, $result);
        }
    }

    protected function createExpectedToken($expr, $reqt = [], $param = false)
    {
        $token = new Token();
        $token->setExpression($expr);
        $token->setRequirements($reqt);
        $token->setParameter($param);

        return $token;
    }

    public function tokenProvider()
    {
        $tokens = [];

        $channelToken = $this->createExpectedToken('channel');
        $userToken = $this->createExpectedToken('user');

        $tokens[] = [
            $channelToken,
            $userToken,
            $this->createExpectedToken('foo-bar'),
        ];

        $tokens[] = [
            $channelToken,
            $userToken,
            $this->createExpectedToken('all'),
        ];

        $tokens[] = [
            $channelToken,
            $userToken,
            $this->createExpectedToken('*'),
        ];

        $tokens[] = [
            $channelToken,
            $userToken,
            $this->createExpectedToken('id', [], true),
        ];

        $tokens[] = [
            $this->createExpectedToken('application'),
            $this->createExpectedToken('name', [], true),
            $this->createExpectedToken('uid', [], true),
        ];

        return [
            ['channel/user/foo-bar', '/', $tokens[0]],
            ['channel:user:all', ':', $tokens[1]],
            ['channel:user:*', ':', $tokens[2]],
            ['channel:user:{id}', ':', $tokens[3]],
            ['application:{name}:{uid}', ':', $tokens[4]],
        ];
    }

    public function testMatch()
    {
        $rc = $this->getRouteCollection();
        $matcher = new Matcher();

        $matched = $matcher->match('channel/user/foo-bar', $rc, '/');
        $this->assertEquals($matched, ['simple_user', $rc->get('simple_user'), []]);

        $matched = $matcher->match('notification/user/admin/123', $rc, '/');
        $this->assertEquals($matched, [
            'user_employee_role_notification',
            $rc->get('user_employee_role_notification'),
            ['role' => 'admin', 'uid' => 123],
        ]);
    }

    public function testMissMatch()
    {
        $this->setExpectedException(ResourceNotFoundException::CLASS);
        $rc = $this->getRouteCollection();
        $matcher = new Matcher();
        $matcher->match('foooo/user/foo-bar', $rc, '/');
    }
}

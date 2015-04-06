<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Tests\PubSubTestCase;
use Gos\Bundle\PubSubRouterBundle\Tokenizer\Token;
use Gos\Bundle\PubSubRouterBundle\Tokenizer\Tokenizer;
use Prophecy\Prophecy\ProphecyInterface;

class MatcherTest extends PubSubTestCase
{
    /** @var  ProphecyInterface */
    protected $routeCollection;

    /** @var  ProphecyInterface */
    protected $tokenizer;

    protected function setUp()
    {
        $this->routeCollection = $this->prophesize(RouteCollection::CLASS);
        $this->tokenizer = $this->prophesize(Tokenizer::CLASS);
    }

    protected function tearDown()
    {
        $this->routeCollection = null;
        $this->tokenizer = null;
    }

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

    public function testMatch()
    {
        $userNotifRoute = $this->createRoute('notification/user/{uid}', 'user_notification', ['uid' => ['pattern' => "\d+", 'wildcard' => true]]);
        $userNotifRoute->setName('user_notification')->shouldBeCalled();
        $userNotifRoute = $userNotifRoute->reveal();

        $this->tokenizer->tokenize($userNotifRoute, '/')->willReturn([
            $this->createToken('notification'),
            $this->createToken('user'),
            $this->createToken('uid', true, ['pattern' => "\d+", 'wildcard' => true]),
        ]);

        $this->tokenizer->tokenize('notification/user/123', '/')->willReturn([
            $this->createToken('notification'),
            $this->createToken('user'),
            $this->createToken('123'),
        ]);

        $this->routeCollection->getIterator()->willReturn(
            new \ArrayIterator([
                'user_notification' => $userNotifRoute,
            ])
        );

        $matcher = new Matcher($this->routeCollection->reveal(), $this->tokenizer->reveal());
        $matched = $matcher->match('notification/user/123', '/');
        $this->assertEquals(['user_notification', $userNotifRoute, ['uid' => '123']], $matched);
    }

    public function testMissMatch()
    {
        $this->setExpectedException(ResourceNotFoundException::CLASS);
        $this->routeCollection->getIterator()->willReturn(new \ArrayIterator([]));
        $matcher = new Matcher($this->routeCollection->reveal(), $this->tokenizer->reveal());
        $matcher->match('notification/user/123', '/');
    }

    /**
     * @dataProvider compareProvider
     */
    public function testCompare(Tokenizer $tokenizer, $route, $path, $separator, $expected)
    {
        $matcher = new Matcher($this->routeCollection->reveal(), $tokenizer);
        $this->assertEquals($this->invokeMethod($matcher, 'compare', [$route, $path, $separator]), $expected);
    }

    /**
     * @param array             $conf
     * @param ProphecyInterface $tokenizer
     * @param Route             $route
     *
     * @return array
     */
    protected function generateResult(Array $conf, ProphecyInterface $tokenizer, Route $route)
    {
        $results = [];

        foreach ($conf['channels'] as $channel) {
            list($path, $separator, $expected) = $channel;

            $rawTokens = explode($separator, $path);
            $tokens = [];

            foreach ($rawTokens as $token) {
                $tokens[] = $this->createToken($token);
            }

            $tokenizer->tokenize($path, $separator)->willReturn($tokens);

            $results[] = [$tokenizer->reveal(), $route, $path, $separator, $expected];
        }

        return $results;
    }

    public function compareProvider()
    {
        $results = [];

        $tests = [
            'user_notification' => ['channels' => [
                    ['notification/user/18', '/', true],
                    ['notification/user/username', '/', false],
                ],
            ],
            'application_notification' => ['channels' => [
                    ['notification/application/*', '/', true],
                    ['notification/application/all', '/', true],
                    ['notification/application/@foo', '/', false],
                ],
            ],
            'user_employee_notification' => ['channels' => [
                    ['notification/user/admin/1', '/', true],
                    ['notification/user/*/azerty', '/', false],
                    ['notification/user/admin/all', '/', false],
                    ['notification/user/*/*', '/', false],
                ],
            ],
            'application_chat_topic' => ['channels' => [
                    ['application/chat/foobar', '/', true],
                ],
            ],
            'redis_user_notification' => ['channels' => [
                    ['notification:user:1233', ':', true],
                    ['notification:user:*', ':', true],
                    ['notification:user:azerty', ':', false],
                ],
            ],
        ];

        foreach ($tests as $routeName => $conf) {
            $tokenizer = $this->prophesize(Tokenizer::CLASS);

            switch ($routeName) {
                case 'redis_user_notification':
                    $route = $this->createRoute(
                        'notification:user:{uid}', 'redis_user_notification', ['uid' => ['pattern' => "\d+", 'wildcard' => true]]
                    )->reveal();

                    $tokenizer->tokenize($route, ':')->willReturn([
                        $this->createToken('notification'),
                        $this->createToken('user'),
                        $this->createToken('uid', true, ['pattern' => "\d+", 'wildcard' => true]),
                    ]);

                    $results += $this->generateResult($conf, $tokenizer, $route);
                break;
                case 'user_notification' :
                    $route = $this->createRoute(
                        'notification/user/{uid}', 'user_notification', ['uid' => ['pattern' => "\d+", 'wildcard' => true]]
                    )->reveal();

                    $tokenizer->tokenize($route, '/')->willReturn([
                        $this->createToken('notification'),
                        $this->createToken('user'),
                        $this->createToken('uid', true, ['pattern' => "\d+", 'wildcard' => true]),
                    ]);

                    $results += $this->generateResult($conf, $tokenizer, $route);
                break;
                case 'application_notification' :
                    $route = $this->createRoute(
                        'notification/application/{aid}',
                        'application_notification',
                        ['aid' => ['pattern' => "\d+", 'wildcard' => true]]
                    )->reveal();

                    $tokenizer->tokenize($route, '/')->willReturn([
                        $this->createToken('notification'),
                        $this->createToken('application'),
                        $this->createToken('aid', true, ['pattern' => "\d+", 'wildcard' => true]),
                    ]);

                    $results += $this->generateResult($conf, $tokenizer, $route);
                    break;
                case 'user_employee_notification':
                    $route = $this->createRoute(
                        'notification/user/{role}/{uid}',
                        'user_employee_role_notification',
                        [
                            'uid' => ['pattern' => "\d+", 'wildcard' => true],
                            'role' => ['pattern' => 'admin|moderator'],
                        ]
                    )->reveal();

                    $tokenizer->tokenize($route, '/')->willReturn([
                        $this->createToken('notification'),
                        $this->createToken('user'),
                        $this->createToken('role', true, ['pattern' => 'admin|moderator']),
                        $this->createToken('uid', true, ['pattern' => "\d+", 'wildcard' => true]),
                    ]);

                    $results += $this->generateResult($conf, $tokenizer, $route);
                break;
                case 'application_chat_topic' :

                    $route = $this->createRoute(
                        'application/chat/{rid}',
                        'application_chat_topic',
                        [
                            'rid' => ['pattern' => "\d+", 'wildcard' => true],
                        ]
                    )->reveal();

                    $tokenizer->tokenize($route, '/')->willReturn([
                        $this->createToken('application'),
                        $this->createToken('chat'),
                        $this->createToken('rid', true, ['pattern' => "\d+", 'wildcard' => true]),
                    ]);

                    $results += $this->generateResult($conf, $tokenizer, $route);
                break;
            }
        }

        return $results;
    }
}

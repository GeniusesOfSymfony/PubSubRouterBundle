<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use PHPUnit\Framework\TestCase;

class RouteCompilerTest extends TestCase
{
    /**
     * @dataProvider provideCompileData
     */
    public function testCompile(string $name, array $arguments, string $prefix, string $regex, array $variables, array $tokens): void
    {
        $route = new Route(...$arguments);

        $compiled = $route->compile();
        $this->assertEquals($prefix, $compiled->getStaticPrefix(), $name.' (static prefix)');
        $this->assertEquals($regex, $compiled->getRegex(), $name.' (regex)');
        $this->assertEquals($variables, $compiled->getVariables(), $name.' (variables)');
        $this->assertEquals($tokens, $compiled->getTokens(), $name.' (tokens)');
    }

    public function provideCompileData(): array
    {
        return [
            [
                'Static route',
                ['foo', 'strlen'],
                'foo',
                '#^foo$#sD',
                [],
                [
                    ['text', 'foo'],
                ],
            ],

            [
                'Route with a variable',
                ['foo/{bar}', 'strlen'],
                'foo',
                '#^foo/(?P<bar>[^/]++)$#sD',
                ['bar'],
                [
                    ['variable', '/', '[^/]++', 'bar'],
                    ['text', 'foo'],
                ],
            ],

            [
                'Route with a variable that has a default value',
                ['foo/{bar}', 'strlen', ['bar' => 'bar']],
                'foo',
                '#^foo(?:/(?P<bar>[^/]++))?$#sD',
                ['bar'],
                [
                    ['variable', '/', '[^/]++', 'bar'],
                    ['text', 'foo'],
                ],
            ],

            [
                'Route with several variables',
                ['foo/{bar}/{foobar}', 'strlen'],
                'foo',
                '#^foo/(?P<bar>[^/]++)/(?P<foobar>[^/]++)$#sD',
                ['bar', 'foobar'],
                [
                    ['variable', '/', '[^/]++', 'foobar'],
                    ['variable', '/', '[^/]++', 'bar'],
                    ['text', 'foo'],
                ],
            ],

            [
                'Route with several variables that have default values',
                ['foo/{bar}/{foobar}', 'strlen', ['bar' => 'bar', 'foobar' => '']],
                'foo',
                '#^foo(?:/(?P<bar>[^/]++)(?:/(?P<foobar>[^/]++))?)?$#sD',
                ['bar', 'foobar'],
                [
                    ['variable', '/', '[^/]++', 'foobar'],
                    ['variable', '/', '[^/]++', 'bar'],
                    ['text', 'foo'],
                ],
            ],

            [
                'Route with several variables but some of them have no default values',
                ['foo/{bar}/{foobar}', 'strlen', ['bar' => 'bar']],
                'foo',
                '#^foo/(?P<bar>[^/]++)/(?P<foobar>[^/]++)$#sD',
                ['bar', 'foobar'],
                [
                    ['variable', '/', '[^/]++', 'foobar'],
                    ['variable', '/', '[^/]++', 'bar'],
                    ['text', 'foo'],
                ],
            ],

            [
                'Route with an optional variable as the first segment',
                ['{bar}', 'strlen', ['bar' => 'bar']],
                '',
                '#^(?P<bar>[^/]++)?$#sD',
                ['bar'],
                [
                    ['variable', '', '[^/]++', 'bar'],
                ],
            ],

            [
                'Route with a requirement of 0',
                ['{bar}', 'strlen', ['bar' => null], ['bar' => '0']],
                '',
                '#^(?P<bar>0)$#sD',
                ['bar'],
                [
                    ['variable', '', '0', 'bar'],
                ],
            ],

            [
                'Route with an optional variable as the first segment with requirements',
                ['{bar}', 'strlen', ['bar' => 'bar'], ['bar' => '(foo|bar)']],
                '',
                '#^(?P<bar>(?:foo|bar))?$#sD',
                ['bar'],
                [
                    ['variable', '', '(?:foo|bar)', 'bar'],
                ],
            ],

            [
                'Route with only optional variables',
                ['{foo}/{bar}', 'strlen', ['foo' => 'foo', 'bar' => 'bar']],
                '',
                '#^(?P<foo>[^/]++)?(?:/(?P<bar>[^/]++))?$#sD',
                ['foo', 'bar'],
                [
                    ['variable', '/', '[^/]++', 'bar'],
                    ['variable', '', '[^/]++', 'foo'],
                ],
            ],

            [
                'Route with a variable in last position',
                ['foo-{bar}', 'strlen'],
                'foo-',
                '#^foo\-(?P<bar>[^/]++)$#sD',
                ['bar'],
                [
                    ['variable', '-', '[^/]++', 'bar'],
                    ['text', 'foo'],
                ],
            ],

            [
                'Route with nested placeholders',
                ['{static{var}static}', 'strlen'],
                '{static',
                '#^\{static(?P<var>[^/]+)static\}$#sD',
                ['var'],
                [
                    ['text', 'static}'],
                    ['variable', '', '[^/]+', 'var'],
                    ['text', '{static'],
                ],
            ],

            [
                'Route without separator between variables',
                ['{w}{x}{y}{z}.{_format}', 'strlen', ['z' => 'default-z', '_format' => 'html'], ['y' => '(y|Y)']],
                '',
                '#^(?P<w>[^/\.]+)(?P<x>[^/\.]+)(?P<y>(?:y|Y))(?:(?P<z>[^/\.]++)(?:\.(?P<_format>[^/]++))?)?$#sD',
                ['w', 'x', 'y', 'z', '_format'],
                [
                    ['variable', '.', '[^/]++', '_format'],
                    ['variable', '', '[^/\.]++', 'z'],
                    ['variable', '', '(?:y|Y)', 'y'],
                    ['variable', '', '[^/\.]+', 'x'],
                    ['variable', '', '[^/\.]+', 'w'],
                ],
            ],

            [
                'Static non UTF-8 route',
                ["/fo\xE9", 'strlen'],
                "/fo\xE9",
                "#^/fo\xE9$#sD",
                [],
                [
                    ['text', "/fo\xE9"],
                ],
            ],

            [
                'Route with an explicit UTF-8 requirement',
                ['{bar}', 'strlen', ['bar' => null], ['bar' => '.'], ['utf8' => true]],
                '',
                '#^(?P<bar>.)$#sDu',
                ['bar'],
                [
                    ['variable', '', '.', 'bar', true],
                ],
            ],
        ];
    }
}

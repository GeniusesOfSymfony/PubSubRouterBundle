<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Router\CompiledRoute;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCompiler;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testConstructor()
    {
        $route = new Route('{foo}', 'strlen', ['foo' => 'bar'], ['foo' => '\d+'], ['foo' => 'bar']);
        $this->assertEquals('{foo}', $route->getPattern(), '__construct() takes a pattern as its first argument');
        $this->assertEquals('strlen', $route->getCallback(), '__construct() takes a callback or string as its second argument');
        $this->assertEquals(
            ['foo' => 'bar'],
            $route->getDefaults(),
            '__construct() takes defaults as its third argument'
        );
        $this->assertEquals(
            ['foo' => '\d+'],
            $route->getRequirements(),
            '__construct() takes requirements as its fourth argument'
        );
        $this->assertEquals('bar', $route->getOption('foo'), '__construct() takes options as its fifth argument');
    }

    public function testPattern()
    {
        $route = new Route('{foo}', 'strlen');
        $route->setPattern('{bar}');
        $this->assertEquals('{bar}', $route->getPattern(), '->setPattern() sets the path');
        $route->setPattern('');
        $this->assertEquals('', $route->getPattern(), '->setPattern() allows an empty path');
    }

    public function testOptions()
    {
        $route = new Route('{foo}', 'strlen');
        $route->setOptions(['foo' => 'bar']);
        $this->assertEquals(
            array_merge(
                [
                    'compiler_class' => RouteCompiler::class,
                ],
                ['foo' => 'bar']
            ),
            $route->getOptions(),
            '->setOptions() sets the options'
        );

        $route->setOptions(['foo' => 'foo']);
        $route->addOptions(['bar' => 'bar']);
        $this->assertEquals(
            ['foo' => 'foo', 'bar' => 'bar', 'compiler_class' => RouteCompiler::class],
            $route->getOptions(),
            '->addDefaults() keep previous defaults'
        );
    }

    public function testOption()
    {
        $route = new Route('{foo}', 'strlen');
        $this->assertFalse($route->hasOption('foo'), '->hasOption() return false if option is not set');
        $route->setOption('foo', 'bar');
        $this->assertEquals('bar', $route->getOption('foo'), '->setOption() sets the option');
        $this->assertTrue($route->hasOption('foo'), '->hasOption() return true if option is set');
    }

    public function testDefaults()
    {
        $route = new Route('{foo}', 'strlen');
        $route->setDefaults(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $route->getDefaults(), '->setDefaults() sets the defaults');

        $route = new Route('{foo}', 'strlen');
        $route->setDefault('foo', 'bar');
        $this->assertEquals('bar', $route->getDefault('foo'), '->setDefault() sets a default value');

        $route->setDefault('foo2', 'bar2');
        $this->assertEquals('bar2', $route->getDefault('foo2'), '->getDefault() return the default value');
        $this->assertNull($route->getDefault('not_defined'), '->getDefault() return null if default value is not set');

        $route->setDefault(
            '_controller',
            $closure = function () {
                return 'Hello';
            }
        );
        $this->assertEquals($closure, $route->getDefault('_controller'), '->setDefault() sets a default value');

        $route->setDefaults(['foo' => 'foo']);
        $route->addDefaults(['bar' => 'bar']);
        $route->addDefaults([]);
        $this->assertEquals(
            ['foo' => 'foo', 'bar' => 'bar'],
            $route->getDefaults(),
            '->addDefaults() keep previous defaults'
        );
    }

    public function testRequirements()
    {
        $route = new Route('{foo}', 'strlen');
        $route->setRequirements(['foo' => '\d+']);
        $this->assertEquals(['foo' => '\d+'], $route->getRequirements(), '->setRequirements() sets the requirements');
        $this->assertEquals('\d+', $route->getRequirement('foo'), '->getRequirement() returns a requirement');
        $this->assertNull(
            $route->getRequirement('bar'),
            '->getRequirement() returns null if a requirement is not defined'
        );
        $route->setRequirements(['foo' => '^\d+$']);
        $this->assertEquals('\d+', $route->getRequirement('foo'), '->getRequirement() removes ^ and $ from the path');

        $route->setRequirements(['foo' => '\d+']);
        $route->addRequirements(['bar' => '\d+']);
        $route->addRequirements([]);
        $this->assertEquals(
            ['foo' => '\d+', 'bar' => '\d+'],
            $route->getRequirements(),
            '->addRequirement() keep previous requirements'
        );
    }

    public function testRequirement()
    {
        $route = new Route('{foo}', 'strlen');
        $this->assertFalse($route->hasRequirement('foo'), '->hasRequirement() return false if requirement is not set');
        $route->setRequirement('foo', '^\d+$');
        $this->assertEquals('\d+', $route->getRequirement('foo'), '->setRequirement() removes ^ and $ from the path');
        $this->assertTrue($route->hasRequirement('foo'), '->hasRequirement() return true if requirement is set');
    }

    /**
     * @dataProvider getInvalidRequirements
     */
    public function testSetInvalidRequirement($req)
    {
        $this->expectException(\InvalidArgumentException::class);

        $route = new Route('{foo}', 'strlen');
        $route->setRequirement('foo', $req);
    }

    public function getInvalidRequirements()
    {
        return [
            [''],
            ['^$'],
            ['^'],
            ['$'],
        ];
    }

    public function testCompile()
    {
        $route = new Route('{foo}', 'strlen');
        $this->assertInstanceOf(CompiledRoute::class, $compiled = $route->compile(), '->compile() returns a compiled route');
        $this->assertSame($compiled, $route->compile(), '->compile() only compiled the route once if unchanged');
        $route->setRequirement('foo', '.*');
        $this->assertNotSame($compiled, $route->compile(), '->compile() recompiles if the route was modified');
    }

    public function testSerialize()
    {
        $route = new Route('prefix/{foo}', 'strlen', ['foo' => 'default'], ['foo' => '\d+']);

        $serialized = serialize($route);
        $unserialized = unserialize($serialized);

        $this->assertEquals($route, $unserialized);
        $this->assertNotSame($route, $unserialized);
    }

    public function testInlineDefaultAndRequirement()
    {
        $this->assertEquals(
            (new Route('foo/{bar}', 'strlen'))->setDefault('bar', null),
            new Route('foo/{bar?}', 'strlen')
        );
        $this->assertEquals(
            (new Route('foo/{bar}', 'strlen'))->setDefault('bar', 'baz'),
            new Route('foo/{bar?baz}', 'strlen')
        );
        $this->assertEquals(
            (new Route('foo/{bar}', 'strlen'))->setDefault('bar', 'baz<buz>'),
            new Route('foo/{bar?baz<buz>}', 'strlen')
        );
        $this->assertEquals(
            (new Route('foo/{bar}', 'strlen'))->setDefault('bar', 'baz'),
            new Route('foo/{bar?}', 'strlen', ['bar' => 'baz'])
        );

        $this->assertEquals(
            (new Route('foo/{bar}', 'strlen'))->setRequirement('bar', '.*'),
            new Route('foo/{bar<.*>}', 'strlen')
        );
        $this->assertEquals(
            (new Route('foo/{bar}', 'strlen'))->setRequirement('bar', '>'),
            new Route('foo/{bar<>>}', 'strlen')
        );
        $this->assertEquals(
            (new Route('foo/{bar}', 'strlen'))->setRequirement('bar', '\d+'),
            new Route('foo/{bar<.*>}', 'strlen', [], ['bar' => '\d+'])
        );
        $this->assertEquals(
            (new Route('foo/{bar}', 'strlen'))->setRequirement('bar', '[a-z]{2}'),
            new Route('foo/{bar<[a-z]{2}>}', 'strlen')
        );

        $this->assertEquals(
            (new Route('foo/{bar}', 'strlen'))->setDefault('bar', null)->setRequirement('bar', '.*'),
            new Route('foo/{bar<.*>?}', 'strlen')
        );
        $this->assertEquals(
            (new Route('foo/{bar}', 'strlen'))->setDefault('bar', '<>')->setRequirement('bar', '>'),
            new Route('foo/{bar<>>?<>}', 'strlen')
        );
    }

    /**
     * Tests that the compiled version is also serialized to prevent the overhead
     * of compiling it again after unserialize.
     */
    public function testSerializeWhenCompiled()
    {
        $route = new Route('prefix/{foo}', 'strlen', ['foo' => 'default'], ['foo' => '\d+']);
        $route->compile();

        $serialized = serialize($route);
        $unserialized = unserialize($serialized);

        $this->assertEquals($route, $unserialized);
        $this->assertNotSame($route, $unserialized);
    }
}

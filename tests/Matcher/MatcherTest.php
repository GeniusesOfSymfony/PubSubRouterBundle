<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Matcher\MatcherInterface;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;

class MatcherTest extends TestCase
{
    public function testMatch(): void
    {
        // test the patterns are matched and parameters are returned
        $collection = new RouteCollection();
        $collection->add('foo', $route = new Route('foo/{bar}', 'strlen'));
        $matcher = $this->getMatcher($collection);

        try {
            $matcher->match('no-match');
            $this->fail();
        } catch (ResourceNotFoundException $e) {
        }

        $this->assertEquals(['foo', $route, ['bar' => 'baz']], $matcher->match('foo/baz'));

        // test that defaults are merged
        $collection = new RouteCollection();
        $collection->add('foo', $route = new Route('foo/{bar}', 'strlen', ['def' => 'test']));
        $matcher = $this->getMatcher($collection);

        $this->assertEquals(['foo', $route, ['bar' => 'baz', 'def' => 'test']], $matcher->match('foo/baz'));

        // route with an optional variable as the first segment
        $collection = new RouteCollection();
        $collection->add('bar', $route = new Route('{bar}/foo', 'strlen', ['bar' => 'bar'], ['bar' => 'foo|bar']));
        $matcher = $this->getMatcher($collection);

        $this->assertEquals(['bar', $route, ['bar' => 'bar']], $matcher->match('bar/foo'));
        $this->assertEquals(['bar', $route, ['bar' => 'foo']], $matcher->match('foo/foo'));

        $collection = new RouteCollection();
        $collection->add('bar', $route = new Route('{bar}', 'strlen', ['bar' => 'bar'], ['bar' => 'foo|bar']));
        $matcher = $this->getMatcher($collection);

        $this->assertEquals(['bar', $route, ['bar' => 'foo']], $matcher->match('foo'));
        $this->assertEquals(['bar', $route, ['bar' => 'bar']], $matcher->match(''));

        // route with only optional variables
        $collection = new RouteCollection();
        $collection->add('bar', $route = new Route('{foo}/{bar}', 'strlen', ['foo' => 'foo', 'bar' => 'bar']));
        $matcher = $this->getMatcher($collection);

        $this->assertEquals(['bar', $route, ['foo' => 'foo', 'bar' => 'bar']], $matcher->match(''));
        $this->assertEquals(['bar', $route, ['foo' => 'a', 'bar' => 'bar']], $matcher->match('a'));
        $this->assertEquals(['bar', $route, ['foo' => 'a', 'bar' => 'b']], $matcher->match('a/b'));
    }

    public function testMatchSpecialRouteName(): void
    {
        $collection = new RouteCollection();
        $collection->add('$péß^a|', $route = new Route('bar', 'strlen'));
        $matcher = $this->getMatcher($collection);
        $this->assertEquals(['$péß^a|', $route, []], $matcher->match('bar'));
    }

    public function testMatchOverriddenRoute(): void
    {
        $collection = new RouteCollection();
        $collection->add('foo', $route = new Route('foo', 'strlen'));

        $collection1 = new RouteCollection();
        $collection1->add('foo', $route = new Route('foo1', 'strlen'));

        $collection->addCollection($collection1);

        $matcher = $this->getMatcher($collection);

        $this->assertEquals(['foo', $route, []], $matcher->match('foo1'));
        $this->expectException(ResourceNotFoundException::class);
        $matcher->match('foo');
    }

    public function testMultipleParams(): void
    {
        $coll = new RouteCollection();
        $coll->add('foo1', new Route('foo/{a}/{b}', 'strlen'));
        $coll->add('foo2', new Route('foo/{a}/test/test/{b}', 'strlen'));
        $coll->add('foo3', new Route('foo/{a}/{b}/{c}/{d}', 'strlen'));

        $attribs = $this->getMatcher($coll)->match('foo/test/test/test/bar');

        $this->assertEquals('foo2', $attribs[0]);
    }

    public function testDefaultRequirementForOptionalVariables(): void
    {
        $coll = new RouteCollection();
        $coll->add('test', $route = new Route('/{page}.{_format}', 'strlen', ['page' => 'index', '_format' => 'html']));

        $matcher = $this->getMatcher($coll);
        $this->assertEquals(
            ['test', $route, ['page' => 'my-page', '_format' => 'xml']],
            $matcher->match('/my-page.xml')
        );
    }

    public function testMatchingIsEager(): void
    {
        $coll = new RouteCollection();
        $coll->add('test', $route = new Route('/{foo}-{bar}-', 'strlen', [], ['foo' => '.+', 'bar' => '.+']));

        $matcher = $this->getMatcher($coll);
        $this->assertEquals(
            ['test', $route, ['foo' => 'text1-text2-text3', 'bar' => 'text4']],
            $matcher->match('/text1-text2-text3-text4-')
        );
    }

    public function testAdjacentVariables(): void
    {
        $coll = new RouteCollection();
        $coll->add(
            'test',
            $route = new Route(
                '{w}{x}{y}{z}.{_format}',
                'strlen',
                ['z' => 'default-z', '_format' => 'html'],
                ['y' => 'y|Y']
            )
        );

        $matcher = $this->getMatcher($coll);

        // 'w' eagerly matches as much as possible and the other variables match the remaining chars.
        // This also shows that the variables w-z must all exclude the separating char (the dot '.' in this case) by default requirement.
        // Otherwise they would also consume '.xml' and _format would never match as it's an optional variable.
        $this->assertEquals(
            ['test', $route, ['w' => 'wwwww', 'x' => 'x', 'y' => 'Y', 'z' => 'Z', '_format' => 'xml']],
            $matcher->match('wwwwwxYZ.xml')
        );

        // As 'y' has custom requirement and can only be of value 'y|Y', it will leave  'ZZZ' to variable z.
        // So with carefully chosen requirements adjacent variables, can be useful.
        $this->assertEquals(
            ['test', $route, ['w' => 'wwwww', 'x' => 'x', 'y' => 'y', 'z' => 'ZZZ', '_format' => 'html']],
            $matcher->match('wwwwwxyZZZ')
        );

        // z and _format are optional.
        $this->assertEquals(
            ['test', $route, ['w' => 'wwwww', 'x' => 'x', 'y' => 'y', 'z' => 'default-z', '_format' => 'html']],
            $matcher->match('wwwwwxy')
        );

        $this->expectException(ResourceNotFoundException::class);
        $matcher->match('wxy.html');
    }

    public function testOptionalVariableWithNoRealSeparator(): void
    {
        $coll = new RouteCollection();
        $coll->add('test', $route = new Route('get{what}', 'strlen', ['what' => 'All']));
        $matcher = $this->getMatcher($coll);

        $this->assertEquals(['test', $route, ['what' => 'All']], $matcher->match('get'));
        $this->assertEquals(['test', $route, ['what' => 'Sites']], $matcher->match('getSites'));

        // Usually the character in front of an optional parameter can be left out, e.g. with pattern '/get/{what}' just '/get' would match.
        // But here the 't' in 'get' is not a separating character, so it makes no sense to match without it.
        $this->expectException(ResourceNotFoundException::class);
        $matcher->match('ge');
    }

    public function testRequiredVariableWithNoRealSeparator(): void
    {
        $coll = new RouteCollection();
        $coll->add('test', $route = new Route('get{what}Suffix', 'strlen'));
        $matcher = $this->getMatcher($coll);

        $this->assertEquals(['test', $route, ['what' => 'Sites']], $matcher->match('getSitesSuffix'));
    }

    public function testDefaultRequirementOfVariable(): void
    {
        $coll = new RouteCollection();
        $coll->add('test', $route = new Route('{page}.{_format}', 'strlen'));
        $matcher = $this->getMatcher($coll);

        $this->assertEquals(
            ['test', $route, ['page' => 'index', '_format' => 'mobile.html']],
            $matcher->match('index.mobile.html')
        );
    }

    public function testDefaultRequirementOfVariableDisallowsSlash(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $coll = new RouteCollection();
        $coll->add('test', new Route('{page}.{_format}', 'strlen'));
        $matcher = $this->getMatcher($coll);

        $matcher->match('index.sl/ash');
    }

    public function testDefaultRequirementOfVariableDisallowsNextSeparator(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $coll = new RouteCollection();
        $coll->add('test', new Route('{page}.{_format}', 'strlen', [], ['_format' => 'html|xml']));
        $matcher = $this->getMatcher($coll);

        $matcher->match('do.t.html');
    }

    public function testNestedCollections(): void
    {
        $coll = new RouteCollection();

        $subColl = new RouteCollection();
        $subColl->add('a', new Route('p/a', 'strlen'));
        $subColl->add('b', new Route('p/b', 'strlen'));
        $subColl->add('c', new Route('p/c', 'strlen'));
        $coll->addCollection($subColl);

        $coll->add('baz', new Route('{baz}', 'strlen'));

        $subColl = new RouteCollection();
        $subColl->add('buz', new Route('prefix/buz', 'strlen'));
        $coll->addCollection($subColl);

        $matcher = $this->getMatcher($coll);

        $this->assertEquals(['a', $coll->get('a'), []], $matcher->match('p/a'));
        $this->assertEquals(['baz', $coll->get('baz'), ['baz' => 'p']], $matcher->match('p'));
        $this->assertEquals(['buz', $coll->get('buz'), []], $matcher->match('prefix/buz'));
    }

    public function testRequirementWithCapturingGroup(): void
    {
        $coll = new RouteCollection();
        $coll->add('a', $route = new Route('{a}/{b}', 'strlen', [], ['a' => '(a|b)']));

        $matcher = $this->getMatcher($coll);

        $this->assertEquals(['a', $route, ['a' => 'a', 'b' => 'b']], $matcher->match('a/b'));
    }

    public function testDotAllWithCatchAll(): void
    {
        $coll = new RouteCollection();
        $coll->add('a', $routeA = new Route('{id}.html', 'strlen', [], ['id' => '.+']));
        $coll->add('b', new Route('{all}', 'strlen', [], ['all' => '.+']));

        $matcher = $this->getMatcher($coll);

        $this->assertEquals(['a', $routeA, ['id' => 'foo/bar']], $matcher->match('foo/bar.html'));
    }

    public function testUtf8Prefix(): void
    {
        $coll = new RouteCollection();
        $coll->add('a', new Route('é{foo}', 'strlen', [], [], ['utf8' => true]));
        $coll->add('b', new Route('è{bar}', 'strlen', [], [], ['utf8' => true]));

        $matcher = $this->getMatcher($coll);

        $this->assertEquals('a', $matcher->match('éo')[0]);
    }

    protected function getMatcher(RouteCollection $routes): MatcherInterface
    {
        return new Matcher($routes);
    }
}

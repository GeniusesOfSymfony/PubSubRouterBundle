<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Generator;

use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    public function testWithoutParameters()
    {
        $routes = $this->getRoutes('test', new Route('testing', 'strlen'));
        $path = $this->getGenerator($routes)->generate('test', []);

        $this->assertEquals('testing', $path);
    }

    public function testWithParameter()
    {
        $routes = $this->getRoutes('test', new Route('testing/{foo}', 'strlen'));
        $path = $this->getGenerator($routes)->generate('test', ['foo' => 'bar']);

        $this->assertEquals('testing/bar', $path);
    }

    /**
     * @expectedException \Gos\Bundle\PubSubRouterBundle\Exception\InvalidParameterException
     */
    public function testWithNullParameterButNotOptional()
    {
        $routes = $this->getRoutes('test', new Route('testing/{foo}/bar', 'strlen', ['foo' => null]));

        // This must raise an exception because the default requirement for "foo" is "[^/]+" which is not met with these params.
        // Generating path "/testing//bar" would be wrong as matching this route would fail.
        $this->getGenerator($routes)->generate('test', []);
    }

    public function testWithOptionalZeroParameter()
    {
        $routes = $this->getRoutes('test', new Route('testing/{page}', 'strlen'));
        $path = $this->getGenerator($routes)->generate('test', ['page' => 0]);

        $this->assertEquals('testing/0', $path);
    }

    public function testNotPassedOptionalParameterInBetween()
    {
        $routes = $this->getRoutes('test', new Route('{slug}/{page}', 'strlen', ['slug' => 'index', 'page' => 0]));
        $this->assertSame('index/1', $this->getGenerator($routes)->generate('test', ['page' => 1]));
        $this->assertSame('', $this->getGenerator($routes)->generate('test'));
    }

    public function testWithExtraParameters()
    {
        $routes = $this->getRoutes('test', new Route('testing', 'strlen'));
        $path = $this->getGenerator($routes)->generate('test', ['foo' => 'bar']);

        $this->assertEquals('testing', $path);
    }

    /**
     * @expectedException \Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException
     */
    public function testGenerateWithoutRoutes()
    {
        $routes = $this->getRoutes('foo', new Route('testing/{foo}', 'strlen'));
        $this->getGenerator($routes)->generate('test', []);
    }

    /**
     * @expectedException \Gos\Bundle\PubSubRouterBundle\Exception\MissingMandatoryParametersException
     */
    public function testGenerateForRouteWithoutMandatoryParameter()
    {
        $routes = $this->getRoutes('test', new Route('testing/{foo}', 'strlen'));
        $this->getGenerator($routes)->generate('test', []);
    }

    /**
     * @expectedException \Gos\Bundle\PubSubRouterBundle\Exception\InvalidParameterException
     */
    public function testGenerateForRouteWithInvalidOptionalParameter()
    {
        $routes = $this->getRoutes('test', new Route('testing/{foo}', 'strlen', ['foo' => '1'], ['foo' => 'd+']));
        $this->getGenerator($routes)->generate('test', ['foo' => 'bar']);
    }

    /**
     * @expectedException \Gos\Bundle\PubSubRouterBundle\Exception\InvalidParameterException
     */
    public function testGenerateForRouteWithInvalidParameter()
    {
        $routes = $this->getRoutes('test', new Route('testing/{foo}', 'strlen', [], ['foo' => '1|2']));
        $this->getGenerator($routes)->generate('test', ['foo' => '0']);
    }

    /**
     * @expectedException \Gos\Bundle\PubSubRouterBundle\Exception\InvalidParameterException
     */
    public function testGenerateForRouteWithInvalidMandatoryParameter()
    {
        $routes = $this->getRoutes('test', new Route('testing/{foo}', 'strlen', [], ['foo' => 'd+']));
        $this->getGenerator($routes)->generate('test', ['foo' => 'bar']);
    }

    /**
     * @expectedException \Gos\Bundle\PubSubRouterBundle\Exception\InvalidParameterException
     */
    public function testGenerateForRouteWithInvalidUtf8Parameter()
    {
        $routes = $this->getRoutes(
            'test',
            new Route('testing/{foo}', 'strlen', [], ['foo' => '\pL+'], ['utf8' => true])
        );
        $this->getGenerator($routes)->generate('test', ['foo' => 'abc123']);
    }

    /**
     * @expectedException \Gos\Bundle\PubSubRouterBundle\Exception\InvalidParameterException
     */
    public function testRequiredParamAndEmptyPassed()
    {
        $routes = $this->getRoutes('test', new Route('{slug}', 'strlen', [], ['slug' => '.+']));
        $this->getGenerator($routes)->generate('test', ['slug' => '']);
    }

    public function testAdjacentVariables()
    {
        $routes = $this->getRoutes('test', new Route('{x}{y}{z}', 'strlen', ['z' => 'default-z'], ['y' => '\d+']));
        $generator = $this->getGenerator($routes);
        $this->assertSame('foo123', $generator->generate('test', ['x' => 'foo', 'y' => '123']));
        $this->assertSame('foo123bar', $generator->generate('test', ['x' => 'foo', 'y' => '123', 'z' => 'bar']));
    }

    public function testOptionalVariableWithNoRealSeparator()
    {
        $routes = $this->getRoutes('test', new Route('get{what}', 'strlen', ['what' => 'All']));
        $generator = $this->getGenerator($routes);

        $this->assertSame('get', $generator->generate('test'));
        $this->assertSame('getSites', $generator->generate('test', ['what' => 'Sites']));
    }

    public function testRequiredVariableWithNoRealSeparator()
    {
        $routes = $this->getRoutes('test', new Route('get{what}Suffix', 'strlen'));
        $generator = $this->getGenerator($routes);

        $this->assertSame('getSitesSuffix', $generator->generate('test', ['what' => 'Sites']));
    }

    public function testDefaultRequirementOfVariable()
    {
        $routes = $this->getRoutes('test', new Route('{page}.{_format}', 'strlen'));
        $generator = $this->getGenerator($routes);

        $this->assertSame(
            'index.mobile.html',
            $generator->generate('test', ['page' => 'index', '_format' => 'mobile.html'])
        );
    }

    /**
     * @expectedException \Gos\Bundle\PubSubRouterBundle\Exception\InvalidParameterException
     */
    public function testDefaultRequirementOfVariableDisallowsSlash()
    {
        $routes = $this->getRoutes('test', new Route('/page}.{_format}', 'strlen'));
        $this->getGenerator($routes)->generate('test', ['page' => 'index', '_format' => 'sl/ash']);
    }

    protected function getGenerator(RouteCollection $routes)
    {
        return new Generator($routes);
    }

    protected function getRoutes($name, Route $route)
    {
        $routes = new RouteCollection();
        $routes->add($name, $route);

        return $routes;
    }
}

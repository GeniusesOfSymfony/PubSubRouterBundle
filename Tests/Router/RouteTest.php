<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteInterface;
use Gos\Bundle\PubSubRouterBundle\Tests\PubSubTestCase;

class RouteTest extends PubSubTestCase
{
    /**
     * @var RouteInterface
     */
    protected $route;

    protected $pattern;

    protected $callback;

    protected $args;

    protected $requirements;

    protected function setUp()
    {
        $this->pattern = 'channel/{id}/*';
        $this->callback = ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'];
        $this->args = ['pusherA', 'pusherB'];
        $this->requirements = ['id' => ['pattern' => '\D+', 'wildcard' => true]];

        $this->route = new Route($this->pattern, $this->callback, $this->args, $this->requirements);
    }

    public function testConstructor()
    {
        $this->assertEquals($this->pattern, $this->readProperty($this->route, 'pattern'));
        $this->assertEquals($this->callback, $this->readProperty($this->route, 'callback'));
        $this->assertEquals($this->args, $this->readProperty($this->route, 'args'));
        $this->assertEquals($this->requirements, $this->readProperty($this->route, 'requirements'));
    }

    public function testGetPattern()
    {
        $this->assertEquals($this->pattern, $this->route->getPattern());
    }

    public function testGetArgs()
    {
        $this->assertEquals($this->args, $this->route->getArgs());
    }

    public function testGetCallback()
    {
        $this->assertEquals($this->callback, $this->route->getCallback());
    }

    public function testGetRequirements()
    {
        $this->assertEquals($this->requirements, $this->route->getRequirements());
    }

    protected function tearDown()
    {
        $this->route = null;
    }
}

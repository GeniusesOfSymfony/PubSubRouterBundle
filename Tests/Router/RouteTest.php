<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Router;

use Gos\Bundle\PubSubRouterBundle\Router\Route;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Route
     */
    protected $route;

    protected $pattern;

    protected $pushers;

    protected $requirements;

    protected function setUp()
    {
        $this->pattern = 'channel/{id}/*';
        $this->pushers = ['pusherA', 'pusherB'];
        $this->requirements = ['id' => ['pattern' => '\D+', 'wildcard' => true]];
        $this->route = new Route($this->pattern, $this->pushers, $this->requirements);
    }

    public function testConstructor()
    {
        $this->assertEquals($this->pattern, \PHPUnit_Framework_Assert::readAttribute($this->route, 'pattern'));
        $this->assertEquals($this->pushers, \PHPUnit_Framework_Assert::readAttribute($this->route, 'pushers'));
        $this->assertEquals($this->requirements, \PHPUnit_Framework_Assert::readAttribute($this->route, 'requirements'));
    }

    public function testGetPattern()
    {
        $this->assertEquals($this->pattern, $this->route->getPattern());
    }

    public function testGetPushers()
    {
        $this->assertEquals($this->pushers, $this->route->getPushers());
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

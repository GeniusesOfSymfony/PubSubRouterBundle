<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Generator\Dumper;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Generator\Dumper\PhpGeneratorDumper;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;

class PhpGeneratorDumperTest extends TestCase
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var PhpGeneratorDumper
     */
    private $generatorDumper;

    /**
     * @var string
     */
    private $testTmpFilepath;

    /**
     * @var string
     */
    private $largeTestTmpFilepath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routeCollection = new RouteCollection();
        $this->generatorDumper = new PhpGeneratorDumper($this->routeCollection);
        $this->testTmpFilepath = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'php_generator.'.$this->getName().'.php';
        $this->largeTestTmpFilepath = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'php_generator.'.$this->getName().'.large.php';
        @unlink($this->testTmpFilepath);
        @unlink($this->largeTestTmpFilepath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink($this->testTmpFilepath);

        $this->routeCollection = null;
        $this->generatorDumper = null;
        $this->testTmpFilepath = null;
    }

    public function testDumpWithRoutes()
    {
        $this->routeCollection->add('Test', new Route('testing/{foo}', 'strlen'));
        $this->routeCollection->add('Test2', new Route('testing2', 'strlen'));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());
        include $this->testTmpFilepath;

        $projectUrlGenerator = new \ProjectGenerator();

        $withParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar']);
        $withoutParameter = $projectUrlGenerator->generate('Test2', []);

        $this->assertEquals('testing/bar', $withParameter);
        $this->assertEquals('testing2', $withoutParameter);
    }

    public function testDumpWithTooManyRoutes()
    {
        $this->routeCollection->add('Test', new Route('testing/{foo}', 'strlen'));
        for ($i = 0; $i < 32769; ++$i) {
            $this->routeCollection->add('route_'.$i, new Route('route_'.$i, 'strlen'));
        }
        $this->routeCollection->add('Test2', new Route('testing2', 'strlen'));

        file_put_contents(
            $this->largeTestTmpFilepath,
            $this->generatorDumper->dump(
                [
                    'class' => 'ProjectLargeGenerator',
                ]
            )
        );
        $this->routeCollection = $this->generatorDumper = null;
        include $this->largeTestTmpFilepath;

        $projectUrlGenerator = new \ProjectLargeGenerator();

        $withParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar']);
        $withoutParameter = $projectUrlGenerator->generate('Test2', []);

        $this->assertEquals('testing/bar', $withParameter);
        $this->assertEquals('testing2', $withoutParameter);
    }

    public function testDumpWithoutRoutes()
    {
        $this->expectException(ResourceNotFoundException::class);

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(['class' => 'WithoutRoutesGenerator']));
        include $this->testTmpFilepath;

        $projectUrlGenerator = new \WithoutRoutesGenerator();

        $projectUrlGenerator->generate('Test', []);
    }

    public function testGenerateNonExistingRoute()
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->routeCollection->add('Test', new Route('test', 'strlen'));

        file_put_contents(
            $this->testTmpFilepath,
            $this->generatorDumper->dump(['class' => 'NonExistingRoutesGenerator'])
        );
        include $this->testTmpFilepath;

        $projectUrlGenerator = new \NonExistingRoutesGenerator();
        $url = $projectUrlGenerator->generate('NonExisting', []);
    }

    public function testDumpForRouteWithDefaults()
    {
        $this->routeCollection->add('Test', new Route('testing/{foo}', 'strlen', ['foo' => 'bar']));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(['class' => 'DefaultRoutesGenerator']));
        include $this->testTmpFilepath;

        $projectUrlGenerator = new \DefaultRoutesGenerator();
        $url = $projectUrlGenerator->generate('Test', []);

        $this->assertEquals('testing', $url);
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Generator\Dumper;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Generator\CompiledGenerator;
use Gos\Bundle\PubSubRouterBundle\Generator\Dumper\CompiledGeneratorDumper;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;

final class CompiledGeneratorDumperTest extends TestCase
{
    /**
     * @var RouteCollection|null
     */
    private $routeCollection = null;

    /**
     * @var CompiledGeneratorDumper|null
     */
    private $generatorDumper = null;

    /**
     * @var string|null
     */
    private $testTmpFilepath = null;

    /**
     * @var string|null
     */
    private $largeTestTmpFilepath = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routeCollection = new RouteCollection();
        $this->generatorDumper = new CompiledGeneratorDumper($this->routeCollection);
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
        $this->largeTestTmpFilepath = null;
    }

    public function testDumpWithRoutes(): void
    {
        $this->routeCollection->add('Test', new Route('testing/{foo}', 'strlen'));
        $this->routeCollection->add('Test2', new Route('testing2', 'strlen'));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());

        $projectUrlGenerator = new CompiledGenerator(require $this->testTmpFilepath);

        $withParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar']);
        $withoutParameter = $projectUrlGenerator->generate('Test2', []);

        $this->assertEquals('testing/bar', $withParameter);
        $this->assertEquals('testing2', $withoutParameter);
    }

    public function testDumpWithTooManyRoutes(): void
    {
        $this->routeCollection->add('Test', new Route('testing/{foo}', 'strlen'));
        for ($i = 0; $i < 32769; ++$i) {
            $this->routeCollection->add('route_'.$i, new Route('route_'.$i, 'strlen'));
        }
        $this->routeCollection->add('Test2', new Route('testing2', 'strlen'));

        file_put_contents($this->largeTestTmpFilepath, $this->generatorDumper->dump());
        $this->routeCollection = $this->generatorDumper = null;

        $projectUrlGenerator = new CompiledGenerator(require $this->largeTestTmpFilepath);

        $withParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar']);
        $withoutParameter = $projectUrlGenerator->generate('Test2', []);

        $this->assertEquals('testing/bar', $withParameter);
        $this->assertEquals('testing2', $withoutParameter);
    }

    /**
     * @group legacy
     */
    public function testDumpWithoutRoutes(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());

        $projectUrlGenerator = new CompiledGenerator(require $this->testTmpFilepath);
        $projectUrlGenerator->generate('Test', []);
    }

    public function testGenerateNonExistingRoute(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->routeCollection->add('Test', new Route('test', 'strlen'));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());

        $projectUrlGenerator = new CompiledGenerator(require $this->testTmpFilepath);
        $projectUrlGenerator->generate('NonExisting', []);
    }

    public function testDumpForRouteWithDefaults(): void
    {
        $this->routeCollection->add('Test', new Route('testing/{foo}', 'strlen', ['foo' => 'bar']));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());

        $projectUrlGenerator = new CompiledGenerator(require $this->testTmpFilepath);

        $url = $projectUrlGenerator->generate('Test', []);

        $this->assertEquals('testing', $url);
    }
}

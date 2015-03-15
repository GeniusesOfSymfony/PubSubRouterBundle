<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\YamlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Yaml\Parser;

class YamlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $fileLocator;

    protected $yamlParser;

    protected $loader;

    protected function setUp()
    {
        $this->fileLocator = $this->prophesize(FileLocator::class);
        $this->fileLocator->locate('@Resource/routes.yml')->willReturn(__DIR__ . '/../Resources/Yaml/routes.yml');

        $this->yamlParser = $yamlParser = $this->prophesize(Parser::CLASS);

        $this->loader = new YamlFileLoader($this->fileLocator->reveal());
    }

    protected function tearDown()
    {
        $this->fileLocator = null;
        $this->yamlParser = null;
        $this->loader = null;
    }

    protected function injectYamlParser()
    {
        $reflection = new \ReflectionClass(YamlFileLoader::CLASS);
        $property = $reflection->getProperty('yamlParser');
        $property->setAccessible(true);
        $property->setValue($this->loader, $this->yamlParser->reveal());
    }

    public function testLoadInvalidConfigurationKeys()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The routing file "' . __DIR__ . '/../Resources/Yaml/routes.yml" contains unsupported keys for "user": "foo". Expected one of: "channel", "pushers", "requirements".'
        );

        $this->yamlParser->parse(file_get_contents(__DIR__ . '/../Resources/Yaml/routes.yml'))
            ->willReturn([
            'user' => [
                'channel' => 'notification/user/{username}',
                'pushers' => [ 'gos_redis', 'gos_websocket' ],
                'foo' => 'bar',
                'requirements' => [
                    'username' => [ 'pattern' => "[a-zA-Z0-9]+", 'wildcard' => true ],
                ],
            ],
        ]);

        $this->injectYamlParser();

        $this->loader->load('@Resource/routes.yml');
    }

    public function testLoadInvalidConfiguration()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid configuration');

        $this->yamlParser->parse(file_get_contents(__DIR__ . '/../Resources/Yaml/routes.yml'))
            ->willReturn('foo');

        $this->injectYamlParser();

        $this->loader->load('@Resource/routes.yml');
    }

    public function testLoad()
    {
        $this->yamlParser->parse(file_get_contents(__DIR__ . '/../Resources/Yaml/routes.yml'))
            ->willReturn([
                'user' => [
                    'channel' => 'notification/user/{username}',
                    'pushers' => [ 'gos_redis', 'gos_websocket' ],
                    'requirements' => [
                        'username' => [ 'pattern' => "[a-zA-Z0-9]+", 'wildcard' => true ],
                    ],
                ],
                'application' => [
                    'channel' => 'notification/application/{applicationName}',
                    'pushers' => [ 'gos_redis', 'gos_websocket' ],
                    'requirements' => [
                        'applicationName' => [ 'pattern' => "[a-zA-Z0-9]+", 'wildcard' => true ],
                    ],
                ],
            ]);

        $this->injectYamlParser();

        $routeCollection = $this->loader->load('@Resource/routes.yml');

        $this->assertEquals([
            'user' => new Route(
                'notification/user/{username}',
                [ 'gos_redis', 'gos_websocket'],
                ['username' => [ 'pattern' => "[a-zA-Z0-9]+", 'wildcard' => true ]]
            ),
            'application' => new Route(
                'notification/application/{applicationName}',
                ['gos_redis', 'gos_websocket' ],
                ['applicationName' => [ 'pattern' => "[a-zA-Z0-9]+", 'wildcard' => true ]]
            ),
        ], \PHPUnit_Framework_Assert::readAttribute($routeCollection, 'routes'));
    }

    public function testSupports()
    {
        $this->assertTrue($this->loader->supports('Resources/test.yml'));
        $this->assertTrue($this->loader->supports('Resources/test.yml', 'yaml'));
        $this->assertFalse($this->loader->supports('Resources/test.xml'));
        $this->assertFalse($this->loader->supports('Resources/test.xml', 'yaml'));
        $this->assertFalse($this->loader->supports('Resources/test'));
        $this->assertFalse($this->loader->supports('Resources/test', 'yaml'));
        $this->assertFalse($this->loader->supports('Resources/test.yaml', 'yaml'));
        $this->assertFalse($this->loader->supports('Resources/test.yaml'));
    }
}

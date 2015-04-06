<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\YamlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Tests\PubSubTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Yaml\Parser;

class YamlFileLoaderTest extends PubSubTestCase
{
    /** @var  FileLocator */
    protected $fileLocator;

    /** @var  Parser */
    protected $yamlParser;

    /** @var  LoaderInterface */
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
        $this->setPropertyValue($this->loader, 'yamlParser', $this->yamlParser->reveal());
    }

    public function testLoadInvalidConfigurationKeys()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The routing file "' . __DIR__ . '/../Resources/Yaml/routes.yml" contains unsupported keys for "user": "foo". Expected one of: "channel", "handler", "requirements".'
        );

        $this->yamlParser->parse(file_get_contents(__DIR__ . '/../Resources/Yaml/routes.yml'))
            ->willReturn([
            'user' => [
                'channel' => 'notification/user/{username}',
                'handler' => [
                    'callback' => ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'],
                    'args' => ['gos_redis', 'gos_websocket'],
                ],
                'foo' => 'bar',
                'requirements' => [
                    'username' => ['pattern' => '[a-zA-Z0-9]+', 'wildcard' => true],
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
                    'handler' => [
                        'callback' => ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'],
                        'args' => ['gos_redis', 'gos_websocket'],
                    ],
                    'requirements' => [
                        'username' => ['pattern' => '[a-zA-Z0-9]+', 'wildcard' => true],
                    ],
                ],
                'application' => [
                    'channel' => 'notification/application/{applicationName}',
                    'handler' => [
                        'callback' => ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'],
                        'args' => ['gos_redis', 'gos_websocket'], ],
                    'requirements' => [
                        'applicationName' => ['pattern' => '[a-zA-Z0-9]+', 'wildcard' => true],
                    ],
                ],
            ]);

        $this->injectYamlParser();

        $routeCollection = $this->loader->load('@Resource/routes.yml');

        $this->assertEquals([
            'user' => new Route(
                'notification/user/{username}',
                ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'],
                ['gos_redis', 'gos_websocket'],
                ['username' => ['pattern' => '[a-zA-Z0-9]+', 'wildcard' => true]]
            ),
            'application' => new Route(
                'notification/application/{applicationName}',
                ['Gos\Bundle\PubSubRouterBundle\Tests\Model', 'setPushers'],
                ['gos_redis', 'gos_websocket'],
                ['applicationName' => ['pattern' => '[a-zA-Z0-9]+', 'wildcard' => true]]
            ),
        ], $this->readProperty($routeCollection, 'routes'));
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

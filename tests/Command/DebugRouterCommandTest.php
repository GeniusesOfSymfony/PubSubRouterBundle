<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Command;

use Gos\Bundle\PubSubRouterBundle\Command\DebugRouterCommand;
use Gos\Bundle\PubSubRouterBundle\Loader\YamlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\Router;
use Gos\Bundle\PubSubRouterBundle\Router\RouterRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Tester\CommandTester;

class DebugRouterCommandTest extends TestCase
{
    public function testCommandListsRoutesForARouter()
    {
        $command = new DebugRouterCommand($this->buildRegistryWithValidRouter());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                '--router_name' => 'test',
            ]
        );

        $this->assertStringEqualsFile(__DIR__.'/../Fixtures/command_output/valid_router.txt', $commandTester->getDisplay());
    }

    public function testCommandRaisesErrorIfRouterDoesNotExist()
    {
        $command = new DebugRouterCommand($this->buildRegistryWithValidRouter());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                '--router_name' => 'missing',
            ]
        );

        $this->assertTrue(false !== strpos($commandTester->getDisplay(), 'Unknown router missing, available routers are [ test ]'));
        $this->assertSame(1, $commandTester->getStatusCode());
    }

    private function buildRegistryWithValidRouter()
    {
        $router = new Router(
            'test',
            new YamlFileLoader(new FileLocator([__DIR__.'/../Fixtures'])),
            [
                'validchannel.yml',
            ]
        );

        $registry = new RouterRegistry();
        $registry->addRouter($router);

        return $registry;
    }
}

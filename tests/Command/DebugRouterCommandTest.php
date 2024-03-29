<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Command;

use Gos\Bundle\PubSubRouterBundle\Command\DebugRouterCommand;
use Gos\Bundle\PubSubRouterBundle\Loader\YamlFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\Router;
use Gos\Bundle\PubSubRouterBundle\Router\RouterRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Tester\CommandCompletionTester;
use Symfony\Component\Console\Tester\CommandTester;

class DebugRouterCommandTest extends TestCase
{
    public function testCommandListsRoutesForARouterWhenGivenTheRouterNameAsAnArgument(): void
    {
        $command = new DebugRouterCommand($this->buildRegistryWithValidRouter());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'router' => 'test',
            ]
        );

        $this->assertStringEqualsFile(__DIR__.'/../Fixtures/command_output/valid_router.txt', $commandTester->getDisplay());
    }

    /**
     * @group legacy
     */
    public function testCommandListsRoutesForARouterWhenGivenTheRouterNameAsAnOption(): void
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

    public function testCommandRaisesErrorIfRouterNameIsNotGiven(): void
    {
        $command = new DebugRouterCommand($this->buildRegistryWithValidRouter());

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertTrue(false !== strpos($commandTester->getDisplay(), 'A router must be provided.'));
        $this->assertSame(1, $commandTester->getStatusCode());
    }

    public function testCommandRaisesErrorIfRouterDoesNotExist(): void
    {
        $command = new DebugRouterCommand($this->buildRegistryWithValidRouter());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'router' => 'missing',
            ]
        );

        $this->assertTrue(false !== strpos($commandTester->getDisplay(), 'Unknown router missing, available routers are [ test ]'));
        $this->assertSame(1, $commandTester->getStatusCode());
    }

    public function testCommandDescribesANamedRouteForARouterWhenGivenTheRouterNameAsAnArgument(): void
    {
        $command = new DebugRouterCommand($this->buildRegistryWithValidRouter());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'router' => 'test',
                'route' => 'user_chat',
            ]
        );

        $this->assertStringEqualsFile(__DIR__.'/../Fixtures/command_output/valid_route.txt', $commandTester->getDisplay());
    }

    public function testCommandDescribesANamedRouteForARouterWhenGivenTheRouterNameAsAnOption(): void
    {
        $command = new DebugRouterCommand($this->buildRegistryWithValidRouter());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                '--router_name' => 'test',
                'router' => 'user_chat',
            ]
        );

        $this->assertStringEqualsFile(__DIR__.'/../Fixtures/command_output/valid_route.txt', $commandTester->getDisplay());
    }

    public function testCommandRaisesErrorIfNamedRouteDoesNotExist(): void
    {
        $command = new DebugRouterCommand($this->buildRegistryWithValidRouter());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'router' => 'test',
                'route' => 'missing',
            ]
        );

        $this->assertTrue(false !== strpos($commandTester->getDisplay(), 'The "missing" route does not exist on the "test" router.'));
        $this->assertSame(1, $commandTester->getStatusCode());
    }

    public function dataCommandAutocompletion(): \Generator
    {
        yield 'argument router' => [
            [''],
            ['test'],
        ];

        yield 'argument route_name for router' => [
            ['test', ''],
            ['user_chat'],
        ];

        yield 'option --format' => [
            ['--format', ''],
            ['json', 'md', 'txt', 'xml'],
        ];
    }

    /**
     * @dataProvider dataCommandAutocompletion
     */
    public function testCommandAutocompletion(array $input, array $suggestions): void
    {
        if (!class_exists(CommandCompletionTester::class)) {
            $this->markTestSkipped('Command autocomplete requires symfony/console 5.4 or later.');
        }

        $tester = new CommandCompletionTester(new DebugRouterCommand($this->buildRegistryWithValidRouter()));

        $this->assertSame($suggestions, $tester->complete($input));
    }

    private function buildRegistryWithValidRouter(): RouterRegistry
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

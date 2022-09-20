<?php

namespace Gos\Bundle\PubSubRouterBundle\Command;

use Gos\Bundle\PubSubRouterBundle\Console\Helper\DescriptorHelper;
use Gos\Bundle\PubSubRouterBundle\Router\RouterRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'gos:pubsub-router:debug', description: 'Display current routes for a PubSub router')]
final class DebugRouterCommand extends Command
{
    protected static $defaultName = 'gos:pubsub-router:debug';
    protected static $defaultDescription = 'Display current routes for a PubSub router';

    public function __construct(private readonly RouterRegistry $registry)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setAliases(['gos:prouter:debug'])
            ->addArgument('router', InputArgument::REQUIRED, 'The router to show information about')
            ->addArgument('route', InputArgument::OPTIONAL, 'An optional route name from the router to describe')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The output format (json, md, txt, or xml)', 'txt')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $routerName */
        $routerName = $input->getArgument('router');

        /** @var string|null $routeName */
        $routeName = $input->getArgument('route');

        if (!$this->registry->hasRouter($routerName)) {
            $io->error(
                sprintf(
                    'Unknown router %s, available routers are [ %s ]',
                    $routerName,
                    implode(', ', array_keys($this->registry->getRouters()))
                )
            );

            return 1;
        }

        $router = $this->registry->getRouter($routerName);

        $helper = new DescriptorHelper();

        if ($routeName) {
            $route = $router->getCollection()->get($routeName);

            if (null === $route) {
                $io->error(sprintf('The "%s" route does not exist on the "%s" router.', $routeName, $routerName));

                return 1;
            }

            $helper->describe(
                $io,
                $route,
                [
                    'format' => $input->getOption('format'),
                    'name' => $routeName,
                    'output' => $io,
                ]
            );
        } else {
            $helper->describe(
                $io,
                $router->getCollection(),
                [
                    'format' => $input->getOption('format'),
                    'output' => $io,
                ]
            );
        }

        return 0;
    }

    /**
     * @throws InvalidArgumentException if an invalid router is provided
     */
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if ($input->mustSuggestArgumentValuesFor('router')) {
            $suggestions->suggestValues(array_keys($this->registry->getRouters()));

            return;
        }

        if ($input->mustSuggestArgumentValuesFor('route')) {
            /** @var string|null $routerName */
            $routerName = $input->getArgument('router');

            if (null !== $routerName) {
                if (!$this->registry->hasRouter($routerName)) {
                    throw new InvalidArgumentException(sprintf('Unknown router %s, available routers are [ %s ]', $routerName, implode(', ', array_keys($this->registry->getRouters()))));
                }

                $router = $this->registry->getRouter($routerName);

                $suggestions->suggestValues(array_keys($router->getCollection()->all()));

                return;
            }
        }

        if ($input->mustSuggestOptionValuesFor('format')) {
            $suggestions->suggestValues((new DescriptorHelper())->getFormats());

            return;
        }
    }
}

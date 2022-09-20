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

/**
 * @final
 */
#[AsCommand(name: 'gos:pubsub-router:debug', description: 'Display current routes for a pubsub router')]
class DebugRouterCommand extends Command
{
    protected static $defaultName = 'gos:pubsub-router:debug';

    /**
     * @var RouterRegistry
     */
    private $registry;

    public function __construct(RouterRegistry $registry)
    {
        parent::__construct();

        $this->registry = $registry;
    }

    protected function configure(): void
    {
        $this
            ->setAliases(['gos:prouter:debug'])
            ->addArgument('router', InputArgument::OPTIONAL, 'The router to show information about')
            ->addArgument('route', InputArgument::OPTIONAL, 'An optional route name from the router to describe')
            ->addOption('router_name', 'r', InputOption::VALUE_REQUIRED, 'Router name')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The output format (json, md, txt, or xml)', 'txt')
            ->setDescription('Display current routes for a pubsub router');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string|null $routerArgument */
        $routerArgument = $input->getArgument('router');

        /** @var string|null $routerOption */
        $routerOption = $input->getOption('router_name');

        /** @var string|null $routeArgument */
        $routeArgument = $input->getArgument('route');

        if (null !== $routerArgument) {
            if (null !== $routerOption) {
                $routerName = $routerOption;
                $routeName = $routerArgument;
            } else {
                $routerName = $routerArgument;
                $routeName = $routeArgument;
            }
        } elseif (null !== $routerOption) {
            trigger_deprecation('gos/pubsub-router-bundle', '2.5', 'The "router_name" option of the "gos:prouter:debug" command is deprecated and will be removed in 3.0, use the router argument instead.');

            $routerName = $routerOption;
            $routeName = null;
        } else {
            $io->error('A router must be provided.');

            return 1;
        }

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
        if ($input->mustSuggestArgumentValuesFor('router') || $input->mustSuggestOptionValuesFor('router_name')) {
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

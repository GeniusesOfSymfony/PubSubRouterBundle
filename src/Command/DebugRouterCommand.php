<?php

namespace Gos\Bundle\PubSubRouterBundle\Command;

use Gos\Bundle\PubSubRouterBundle\Console\Helper\DescriptorHelper;
use Gos\Bundle\PubSubRouterBundle\Router\RouterRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DebugRouterCommand extends Command
{
    protected static $defaultName = 'gos:pubsub-router:debug';

    private RouterRegistry $registry;

    public function __construct(RouterRegistry $registry)
    {
        parent::__construct();

        $this->registry = $registry;
    }

    protected function configure(): void
    {
        $this
            ->setAliases(['gos:prouter:debug'])
            ->addArgument('router', InputArgument::REQUIRED, 'The router to show information about')
            ->addArgument('route', InputArgument::OPTIONAL, 'An optional route name from the router to describe')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The output format (json, md, txt, or xml)', 'txt')
            ->setDescription('Display current routes for a pubsub router');
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
}

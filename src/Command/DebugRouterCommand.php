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

/**
 * @final
 */
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
            ->addOption('router_name', 'r', InputOption::VALUE_REQUIRED, 'Router name')
            ->setDescription('Display current routes for a pubsub router');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string|null $routerArgument */
        $routerArgument = $input->getArgument('router');

        /** @var string|null $routerOption */
        $routerOption = $input->getOption('router_name');

        if (null !== $routerArgument) {
            $routerName = $routerArgument;
        } elseif (null !== $routerOption) {
            trigger_deprecation('gos/pubsub-router-bundle', '2.5', 'The "router_name" option of the "gos:prouter:debug" command is deprecated and will be removed in 3.0, use the router argument instead.');

            $routerName = $routerOption;
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

        $helper->describe(
            $io,
            $router->getCollection(),
            [
                'output' => $io,
            ]
        );

        return 0;
    }
}

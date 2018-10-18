<?php

namespace Gos\Bundle\PubSubRouterBundle\Command;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouterRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugRouterCommand extends Command
{
    protected static $defaultName = 'gos:prouter:debug';

    /**
     * @var RouterRegistry
     */
    private $registry;

    public function __construct(RouterRegistry $registry)
    {
        parent::__construct();

        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption('router_name', 'r', InputOption::VALUE_REQUIRED, 'Router name')
            ->setDescription('Dump route definitions');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $rname = $input->getOption('router_name');

        if (!$this->registry->hasRouter($rname)) {
            $io->error(
                sprintf(
                    'Unknown router %s, available routers are [ %s ]',
                    $rname,
                    implode(', ', array_keys($this->registry->getRouters()))
                )
            );

            return 1;
        }

        $router = $this->registry->getRouter($rname);

        $table = new Table($output);
        $table->setHeaders(['Name', 'Pattern', 'Callback']);

        /**
         * @var string $name
         * @var Route  $route
         */
        foreach ($router->getCollection() as $name => $route) {
            if (is_array($route->getCallback())) {
                $callback = implode(', ', $route->getCallback());
            } elseif (is_callable($route->getCallback())) {
                $callback = (string) $route->getCallback();
            } else {
                $callback = $route->getCallback();
            }

            $table->addRow([$name, $route->getPattern(), $callback]);
        }

        $table->render();
    }
}

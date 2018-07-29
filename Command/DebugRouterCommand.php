<?php

namespace Gos\Bundle\PubSubRouterBundle\Command;

use Gos\Bundle\PubSubRouterBundle\Router\RouteInterface;
use Gos\Bundle\PubSubRouterBundle\Router\RouterInterface;
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
     * @var RouterInterface[]
     */
    protected $routers = [];

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
     * @param RouterInterface $router
     */
    public function addRouter(RouterInterface $router)
    {
        $this->routers[$router->getName()] = $router;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $rname = $input->getOption('router_name');

        if (!isset($this->routers[$rname])) {
            $io->error(sprintf('Unknown router %s, available routers are [ %s ]', $rname, implode(', ', array_keys($this->routers))));

            return 1;
        }

        $router = $this->routers[$rname];

        $table = new Table($output);
        $table->setHeaders(['Name', 'Pattern', 'Callback']);

        /**
         * @var string $name
         * @var RouteInterface $route
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

<?php

namespace Gos\Bundle\PubSubRouterBundle\Command;

use Gos\Bundle\PubSubRouterBundle\Router\RouterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DebugRouterCommand extends Command
{
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
            ->setName('gos:prouter:debug')
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
        $rname = $input->getOption('router_name');

        if (!isset($this->routers[$rname])) {
            $output->writeln('<error>' . sprintf('Unknown router %s, available are [ %s ]', $rname, implode(', ', array_keys($this->routers))) . '</error>');
            exit(1);
        }

        $router = $this->routers[$rname];

        $collection = $router->getCollection();
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(['Name', 'Pattern', 'Callback']);

        $rows = [];

        /*
         * @var string
         * @var RouteInterface
         */
        foreach ($collection as $name => $route) {
            if (is_array($route->getCallback())) {
                $callback = implode(', ', $route->getCallback());
            } elseif (is_callable($route->getCallback())) {
                $callback = (string) $route->getCallback();
            } else {
                $callback = $route->getCallback();
            }

            $rows[] = [$name,  $route->getPattern(), $callback];
        }

        $table->setRows($rows);

        $table->render($output);
    }
}

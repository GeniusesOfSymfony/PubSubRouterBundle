<?php

namespace Gos\Bundle\PubSubRouterBundle\Command;

use Gos\Bundle\PubSubRouterBundle\Router\RouteInterface;
use Gos\Bundle\PubSubRouterBundle\Router\RouterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugRouterCommand extends Command
{
    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('gos:prouter:debug')
            ->setDescription('Dump route definitions');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $this->router->getCollection();
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(['Name', 'Pattern', 'Callback']);
        $table->setLayout(TableHelper::LAYOUT_COMPACT);

        $rows = [];
        /*
         * @var string
         * @var RouteInterface
         */
        foreach ($collection as $name => $route) {
            if (is_array($route->getCallback())) {
                $callback = implode(', ', $route->getCallback());
            } else {
                $callback = $route->getCallback();
            }

            $rows[] = [$name,  $route->getPattern(), $callback];
        }

        $table->setRows($rows);

        $table->render($output);
    }
}

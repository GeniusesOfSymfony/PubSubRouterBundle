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

    protected function configure(): void
    {
        $this
            ->addOption('router_name', 'r', InputOption::VALUE_REQUIRED, 'Router name')
            ->setDescription('Dump route definitions');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $rname */
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
            if (\is_array($route->getCallback())) {
                $callback = implode(', ', $route->getCallback());
            } elseif (\is_callable($route->getCallback())) {
                $callback = $this->formatCallable($route->getCallback());
            } else {
                $callback = $route->getCallback();
            }

            $table->addRow([$name, $route->getPattern(), $callback]);
        }

        $table->render();

        return 0;
    }

    /**
     * @param callable $callable
     */
    private function formatCallable($callable): string
    {
        if (\is_array($callable)) {
            if (\is_object($callable[0])) {
                return sprintf('%s::%s()', \get_class($callable[0]), $callable[1]);
            }

            return sprintf('%s::%s()', $callable[0], $callable[1]);
        }

        if (\is_string($callable)) {
            return sprintf('%s()', $callable);
        }

        if ($callable instanceof \Closure) {
            $r = new \ReflectionFunction($callable);
            if (false !== strpos($r->name, '{closure}')) {
                return 'Closure()';
            }
            if ($class = $r->getClosureScopeClass()) {
                return sprintf('%s::%s()', $class->name, $r->name);
            }

            return $r->name.'()';
        }

        if (is_object($callable) && method_exists($callable, '__invoke')) {
            return sprintf('%s::__invoke()', \get_class($callable));
        }

        throw new \InvalidArgumentException('Callable is not describable.');
    }
}

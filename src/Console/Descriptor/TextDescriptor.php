<?php

namespace Gos\Bundle\PubSubRouterBundle\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Console\Helper\Table;

final class TextDescriptor extends Descriptor
{
    protected function describeRouteCollection(RouteCollection $routes, array $options = []): void
    {
        $tableHeaders = ['Name', 'Pattern', 'Callback'];
        $tableRows = [];

        foreach ($routes->all() as $name => $route) {
            if (\is_array($route->getCallback())) {
                $callback = implode(', ', $route->getCallback());
            } elseif (\is_callable($route->getCallback())) {
                $callback = $this->formatCallable($route->getCallback());
            } else {
                $callback = $route->getCallback();
            }

            $tableRows[] = [$name, $route->getPattern(), $callback];
        }

        if (isset($options['output'])) {
            $options['output']->table($tableHeaders, $tableRows);
        } else {
            (new Table($this->getOutput()))
                ->setHeaders($tableHeaders)
                ->setRows($tableRows)
                ->render();
        }
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

        if (\is_object($callable) && method_exists($callable, '__invoke')) {
            return sprintf('%s::__invoke()', \get_class($callable));
        }

        throw new \InvalidArgumentException('Callable is not describable.');
    }
}

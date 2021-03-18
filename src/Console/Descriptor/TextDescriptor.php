<?php

namespace Gos\Bundle\PubSubRouterBundle\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Console\Helper\Table;

/**
 * @internal
 */
final class TextDescriptor extends Descriptor
{
    protected function describeRouteCollection(RouteCollection $routes, array $options = []): void
    {
        $tableHeaders = ['Name', 'Pattern', 'Callback'];
        $tableRows = [];

        foreach ($routes->all() as $name => $route) {
            $tableRows[] = [$name, $route->getPattern(), $this->formatRouteCallback($route)];
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

    protected function describeRoute(Route $route, array $options = []): void
    {
        $tableHeaders = ['Property', 'Value'];

        $tableRows = [
            ['Route Name', $options['name'] ?? ''],
            ['Pattern', $route->getPattern()],
            ['Pattern Regex', $route->compile()->getRegex()],
            ['Callback', $this->formatRouteCallback($route)],
            ['Requirements', ($route->getRequirements() ? $this->formatRouterConfig($route->getRequirements()) : 'NO CUSTOM')],
            ['Class', \get_class($route)],
            ['Defaults', $this->formatRouterConfig($route->getDefaults())],
            ['Options', $this->formatRouterConfig($route->getOptions())],
        ];

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

    private function formatRouteCallback(Route $route): string
    {
        if (\is_array($route->getCallback())) {
            return implode(', ', $route->getCallback());
        }

        if (\is_callable($route->getCallback())) {
            return $this->formatCallable($route->getCallback());
        }

        return $route->getCallback();
    }

    private function formatRouterConfig(array $config): string
    {
        if (empty($config)) {
            return 'NONE';
        }

        ksort($config);

        $configAsString = '';

        foreach ($config as $key => $value) {
            $configAsString .= sprintf("\n%s: %s", $key, $this->formatValue($value));
        }

        return trim($configAsString);
    }
}

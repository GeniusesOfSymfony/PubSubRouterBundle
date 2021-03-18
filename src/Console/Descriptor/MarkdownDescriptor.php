<?php

namespace Gos\Bundle\PubSubRouterBundle\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Console\Helper\Table;

/**
 * @internal
 */
final class MarkdownDescriptor extends Descriptor
{
    protected function describeRouteCollection(RouteCollection $routes, array $options = []): void
    {
        $first = true;

        foreach ($routes->all() as $name => $route) {
            if ($first) {
                $first = false;
            } else {
                $this->write("\n\n");
            }

            $this->describeRoute($route, ['name' => $name]);
        }

        $this->write("\n");
    }

    protected function describeRoute(Route $route, array $options = []): void
    {
        $output = '- Pattern: '.$route->getPattern()
            ."\n".'- Pattern Regex: '.$route->compile()->getRegex()
            ."\n".'- Callback: '.$this->formatRouteCallback($route)
            ."\n".'- Requirements: '.($route->getRequirements() ? $this->formatRouterConfig($route->getRequirements()) : 'NO CUSTOM')
            ."\n".'- Class: '.\get_class($route)
            ."\n".'- Defaults: '.$this->formatRouterConfig($route->getDefaults())
            ."\n".'- Options: '.$this->formatRouterConfig($route->getOptions());

        $this->write(isset($options['name'])
            ? $options['name']."\n".str_repeat('-', \strlen($options['name']))."\n\n".$output
            : $output);
        $this->write("\n");
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

    private function formatRouterConfig(array $array): string
    {
        if (!$array) {
            return 'NONE';
        }

        $string = '';
        ksort($array);

        foreach ($array as $name => $value) {
            $string .= "\n".'    - `'.$name.'`: '.$this->formatValue($value);
        }

        return $string;
    }
}

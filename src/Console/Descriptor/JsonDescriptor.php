<?php

namespace Gos\Bundle\PubSubRouterBundle\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Console\Helper\Table;

/**
 * @internal
 */
final class JsonDescriptor extends Descriptor
{
    protected function describeRouteCollection(RouteCollection $routes, array $options = []): void
    {
        $data = [];

        foreach ($routes->all() as $name => $route) {
            $data[$name] = $this->getRouteData($route);
        }

        $this->writeData($data, $options);
    }

    protected function describeRoute(Route $route, array $options = []): void
    {
        $this->writeData($this->getRouteData($route), $options);
    }

    /**
     * @return array<string, mixed>
     */
    private function getRouteData(Route $route): array
    {
        return [
            'pattern' => $route->getPattern(),
            'patternRegex' => $route->compile()->getRegex(),
            'callback' => $this->formatRouteCallback($route),
            'requirements' => $route->getRequirements() ?: 'NO CUSTOM',
            'class' => \get_class($route),
            'defaults' => $route->getDefaults(),
            'options' => $route->getOptions(),
        ];
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

    private function writeData(array $data, array $options): void
    {
        $flags = $options['json_encoding'] ?? 0;

        $this->write(json_encode($data, $flags | \JSON_PRETTY_PRINT)."\n");
    }
}

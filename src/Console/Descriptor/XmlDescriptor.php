<?php

namespace Gos\Bundle\PubSubRouterBundle\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Console\Helper\Table;

/**
 * @internal
 */
final class XmlDescriptor extends Descriptor
{
    protected function describeRouteCollection(RouteCollection $routes, array $options = []): void
    {
        $this->writeDocument($this->getRouteCollectionDocument($routes));
    }

    protected function describeRoute(Route $route, array $options = []): void
    {
        $this->writeDocument($this->getRouteDocument($route, $options['name'] ?? null));
    }

    private function getRouteCollectionDocument(RouteCollection $routes): \DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($routesXML = $dom->createElement('routes'));

        foreach ($routes->all() as $name => $route) {
            $routeXML = $this->getRouteDocument($route, $name);
            $routesXML->appendChild($routesXML->ownerDocument->importNode($routeXML->childNodes->item(0), true));
        }

        return $dom;
    }

    private function getRouteDocument(Route $route, string $name = null): \DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($routeXML = $dom->createElement('route'));

        if ($name) {
            $routeXML->setAttribute('name', $name);
        }

        $routeXML->setAttribute('class', \get_class($route));
        $routeXML->setAttribute('callback', $this->formatRouteCallback($route));

        $routeXML->appendChild($patternXML = $dom->createElement('path'));
        $patternXML->setAttribute('regex', $route->compile()->getRegex());
        $patternXML->appendChild(new \DOMText($route->getPattern()));

        if ($route->getDefaults()) {
            $routeXML->appendChild($defaultsXML = $dom->createElement('defaults'));

            foreach ($route->getDefaults() as $attribute => $value) {
                $defaultsXML->appendChild($defaultXML = $dom->createElement('default'));
                $defaultXML->setAttribute('key', $attribute);
                $defaultXML->appendChild(new \DOMText($this->formatValue($value)));
            }
        }

        if ($route->getRequirements()) {
            $routeXML->appendChild($requirementsXML = $dom->createElement('requirements'));

            foreach ($route->getRequirements() as $attribute => $pattern) {
                $requirementsXML->appendChild($requirementXML = $dom->createElement('requirement'));
                $requirementXML->setAttribute('key', $attribute);
                $requirementXML->appendChild(new \DOMText($pattern));
            }
        }

        if ($route->getOptions()) {
            $routeXML->appendChild($optionsXML = $dom->createElement('options'));

            foreach ($route->getOptions() as $name => $value) {
                $optionsXML->appendChild($optionXML = $dom->createElement('option'));
                $optionXML->setAttribute('key', $name);
                $optionXML->appendChild(new \DOMText($this->formatValue($value)));
            }
        }

        return $dom;
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

    private function writeDocument(\DOMDocument $dom): void
    {
        $dom->formatOutput = true;
        $this->write($dom->saveXML());
    }
}

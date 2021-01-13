<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Util\XmlUtils;

final class XmlFileLoader extends CompatibilityFileLoader
{
    const NAMESPACE_URI = 'https://github.com/GeniusesOfSymfony/PubSubRouterBundle/schema/routing';
    const SCHEME_PATH = '/schema/routing/routing-1.0.xsd';

    /**
     * @throws \InvalidArgumentException when the file cannot be loaded or when the XML cannot be parsed because it does not validate against the scheme
     */
    protected function doLoad($resource, string $type = null): RouteCollection
    {
        $path = $this->locator->locate($resource);

        $xml = $this->loadFile($path);

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // process routes and imports
        foreach ($xml->documentElement->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            $this->parseNode($collection, $node, $path, $resource);
        }

        return $collection;
    }

    /**
     * @throws \InvalidArgumentException When the XML is invalid
     */
    private function parseNode(RouteCollection $collection, \DOMElement $node, string $path, string $file): void
    {
        if (self::NAMESPACE_URI !== $node->namespaceURI) {
            return;
        }

        switch ($node->localName) {
            case 'route':
                $this->parseRoute($collection, $node, $path);
                break;

            case 'import':
                $this->parseImport($collection, $node, $path, $file);
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "route" or "import".', $node->localName, $path));
        }
    }

    /**
     * @param mixed $resource
     */
    protected function doSupports($resource, string $type = null): bool
    {
        return \is_string($resource) && 'xml' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'xml' === $type);
    }

    /**
     * @throws \InvalidArgumentException When the XML is invalid
     */
    private function parseRoute(RouteCollection $collection, \DOMElement $node, string $filepath): void
    {
        if ('' === $id = $node->getAttribute('id')) {
            throw new \InvalidArgumentException(sprintf('The <route> element in file "%s" must have an "id" attribute.', $filepath));
        }

        if ($node->hasAttribute('pattern') && $node->hasAttribute('channel')) {
            throw new \InvalidArgumentException(sprintf('The routing file "%s" requires that both the "pattern" attribute and the "channel" attribute cannot be set for route ID "%s".', $filepath, $id));
        } elseif ($node->hasAttribute('channel')) {
            trigger_deprecation('gos/pubsub-router-bundle', '2.4', 'The routing file "%s" uses the deprecated "channel" attribute for route ID "%s" and will not be supported in 3.0, use the "pattern" key instead.', $filepath, $id);
        } elseif (!$node->hasAttribute('pattern')) {
            throw new \InvalidArgumentException(sprintf('The routing file "%s" requires the "pattern" attribute for route ID "%s".', $filepath, $id));
        }

        if ($node->hasAttribute('pattern')) {
            $pattern = $node->getAttribute('pattern');
        } else {
            $pattern = $node->getAttribute('channel');
        }

        if (!$node->hasAttribute('callback')) {
            throw new \InvalidArgumentException(sprintf('The routing file "%s" requires the "callback" attribute for route ID "%s".', $filepath, $id));
        }

        $callback = $node->getAttribute('callback');

        [$defaults, $requirements, $options] = $this->parseConfigs($node, $filepath);

        $route = new Route($pattern, $callback);
        $route->addDefaults($defaults);
        $route->addRequirements($requirements);
        $route->addOptions($options);

        $collection->add($id, $route);
    }

    /**
     * @throws \InvalidArgumentException When the XML is invalid
     */
    private function parseImport(RouteCollection $collection, \DOMElement $node, string $path, string $file): void
    {
        if ('' === $resource = $node->getAttribute('resource')) {
            throw new \InvalidArgumentException(sprintf('The <import> element in file "%s" must have a "resource" attribute.', $path));
        }

        $type = $node->getAttribute('type');

        [$defaults, $requirements, $options] = $this->parseConfigs($node, $path);

        $exclude = [];

        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === $exclude && self::NAMESPACE_URI === $child->namespaceURI) {
                $exclude[] = $child->nodeValue;
            }
        }

        if ($node->hasAttribute('exclude')) {
            if ($exclude) {
                throw new \InvalidArgumentException('You cannot use both the attribute "exclude" and <exclude> tags at the same time.');
            }

            $exclude = [$node->getAttribute('exclude')];
        }

        $this->setCurrentDir(\dirname($path));

        /** @var RouteCollection[] $imported */
        $imported = $this->import($resource, ('' !== $type ? $type : null), false, $file, $exclude) ?: [];

        if (!\is_array($imported)) {
            $imported = [$imported];
        }

        foreach ($imported as $subCollection) {
            $subCollection->addDefaults($defaults);
            $subCollection->addRequirements($requirements);
            $subCollection->addOptions($options);

            $collection->addCollection($subCollection);
        }
    }

    /**
     * @throws \InvalidArgumentException When loading of XML file fails because of syntax errors or when the XML structure is not as expected by the scheme
     */
    private function loadFile(string $file): \DOMDocument
    {
        return XmlUtils::loadFile($file, __DIR__.static::SCHEME_PATH);
    }

    /**
     * Parses the config elements (default, requirement, option).
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    private function parseConfigs(\DOMElement $node, string $path): array
    {
        $defaults = [];
        $requirements = [];
        $options = [];

        /** @var \DOMElement $n */
        foreach ($node->getElementsByTagNameNS(self::NAMESPACE_URI, '*') as $n) {
            if ($node !== $n->parentNode) {
                continue;
            }

            switch ($n->localName) {
                case 'default':
                    if ($this->isElementValueNull($n)) {
                        $defaults[$n->getAttribute('key')] = null;
                    } else {
                        $defaults[$n->getAttribute('key')] = $this->parseDefaultsConfig($n, $path);
                    }

                    break;

                case 'requirement':
                    $requirements[$n->getAttribute('key')] = trim($n->textContent);
                    break;

                case 'option':
                    $options[$n->getAttribute('key')] = XmlUtils::phpize(trim($n->textContent));
                    break;

                default:
                    throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "default", "requirement", "option".', $n->localName, $path));
            }
        }

        return [$defaults, $requirements, $options];
    }

    /**
     * Parses the "default" elements.
     *
     * @return array|bool|float|int|string|null The parsed value of the "default" element
     */
    private function parseDefaultsConfig(\DOMElement $element, string $path)
    {
        if ($this->isElementValueNull($element)) {
            return null;
        }

        // Check for existing element nodes in the default element. There can
        // only be a single element inside a default element. So this element
        // (if one was found) can safely be returned.
        foreach ($element->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            if (self::NAMESPACE_URI !== $child->namespaceURI) {
                continue;
            }

            return $this->parseDefaultNode($child, $path);
        }

        // If the default element doesn't contain a nested "bool", "int", "float",
        // "string", "list", or "map" element, the element contents will be treated
        // as the string value of the associated default option.
        return trim($element->textContent);
    }

    /**
     * Recursively parses the value of a "default" element.
     *
     * @return array|bool|float|int|string|null The parsed value
     *
     * @throws \InvalidArgumentException when the XML is invalid
     */
    private function parseDefaultNode(\DOMElement $node, string $path)
    {
        if ($this->isElementValueNull($node)) {
            return null;
        }

        switch ($node->localName) {
            case 'bool':
                return 'true' === trim($node->nodeValue) || '1' === trim($node->nodeValue);
            case 'int':
                return (int) trim($node->nodeValue);
            case 'float':
                return (float) trim($node->nodeValue);
            case 'string':
                return trim($node->nodeValue);
            case 'list':
                $list = [];

                foreach ($node->childNodes as $element) {
                    if (!$element instanceof \DOMElement) {
                        continue;
                    }

                    if (self::NAMESPACE_URI !== $element->namespaceURI) {
                        continue;
                    }

                    $list[] = $this->parseDefaultNode($element, $path);
                }

                return $list;
            case 'map':
                $map = [];

                foreach ($node->childNodes as $element) {
                    if (!$element instanceof \DOMElement) {
                        continue;
                    }

                    if (self::NAMESPACE_URI !== $element->namespaceURI) {
                        continue;
                    }

                    $map[$element->getAttribute('key')] = $this->parseDefaultNode($element, $path);
                }

                return $map;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "bool", "int", "float", "string", "list", or "map".', $node->localName, $path));
        }
    }

    private function isElementValueNull(\DOMElement $element): bool
    {
        $namespaceUri = 'http://www.w3.org/2001/XMLSchema-instance';

        if (!$element->hasAttributeNS($namespaceUri, 'nil')) {
            return false;
        }

        return 'true' === $element->getAttributeNS($namespaceUri, 'nil') || '1' === $element->getAttributeNS($namespaceUri, 'nil');
    }
}

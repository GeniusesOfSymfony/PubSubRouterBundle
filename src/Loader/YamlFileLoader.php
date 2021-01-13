<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 * @final
 */
class YamlFileLoader extends CompatibilityFileLoader
{
    private const AVAILABLE_KEYS = [
        'resource',
        'type',
        'channel',
        'pattern',
        'handler',
        'callback',
        'defaults',
        'requirements',
        'options',
        'exclude',
    ];

    /**
     * @var YamlParser
     */
    private $yamlParser;

    /**
     * @param mixed $resource
     *
     * @throws \InvalidArgumentException if the resource cannot be processed
     */
    protected function doLoad($resource, string $type = null): RouteCollection
    {
        $path = $this->locator->locate($resource);

        if (!stream_is_local($path)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        try {
            $config = $this->yamlParser->parseFile($path, Yaml::PARSE_CONSTANT);
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML: %s', $path, $e->getMessage()), 0, $e);
        }

        $routeCollection = new RouteCollection();
        $routeCollection->addResource(new FileResource($path));

        // empty file
        if (null === $config) {
            return $routeCollection;
        }

        // not an array
        if (!\is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        foreach ($config as $routeName => $routeConfig) {
            $this->validate($routeConfig, $routeName, $path);

            if (isset($routeConfig['resource'])) {
                $this->parseImport($routeCollection, $routeConfig, $path, $resource);
            } else {
                $this->parseRoute($routeCollection, $routeName, $routeConfig, $path);
            }
        }

        return $routeCollection;
    }

    /**
     * Parses an import and adds the routes in the resource to the RouteCollection.
     *
     * @param array  $config Route definition
     * @param string $path   Full path of the YAML file being processed
     * @param string $file   Loaded file name
     */
    protected function parseImport(RouteCollection $collection, array $config, string $path, string $file): void
    {
        $type = $config['type'] ?? null;
        $defaults = $config['defaults'] ?? [];
        $requirements = $config['requirements'] ?? [];
        $options = $config['options'] ?? [];
        $exclude = $config['exclude'] ?? null;

        $this->setCurrentDir(\dirname($path));

        /** @var RouteCollection[] $imported */
        $imported = $this->import($config['resource'], $type, false, $file, $exclude) ?: [];

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
     * Parses a route and adds it to the RouteCollection.
     *
     * @param string $name   Route name
     * @param array  $config Route definition
     * @param string $path   Full path of the YAML file being processed
     */
    protected function parseRoute(RouteCollection $collection, string $name, array $config, string $path): void
    {
        $defaults = $config['defaults'] ?? [];
        $requirements = $config['requirements'] ?? [];
        $options = $config['options'] ?? [];

        foreach ($requirements as $placeholder => $requirement) {
            if (\is_int($placeholder)) {
                throw new \InvalidArgumentException(sprintf('A placeholder name must be a string (%d given). Did you forget to specify the placeholder key for the requirement "%s" of route "%s" in "%s"?', $placeholder, $requirement, $name, $path));
            }
        }

        if (isset($config['pattern'])) {
            $pattern = $config['pattern'];
        } else {
            $pattern = $config['channel'];
        }

        if (isset($config['callback'])) {
            $callback = $config['callback'];
        } else {
            $callback = $config['handler'];
        }

        $route = new Route($pattern, $callback);
        $route->addDefaults($defaults);
        $route->addRequirements($requirements);
        $route->addOptions($options);

        $collection->add($name, $route);
    }

    /**
     * @throws \InvalidArgumentException if the data is invalid
     */
    protected function validate(array $config, string $name, string $path): void
    {
        if (!\is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The definition of "%s" in "%s" must be a YAML array.', $name, $path));
        }

        if ($extraKeys = array_diff(array_keys($config), self::AVAILABLE_KEYS)) {
            throw new \InvalidArgumentException(sprintf('The routing file "%s" contains unsupported keys for "%s": "%s". Expected one of: "%s".', $path, $name, implode('", "', $extraKeys), implode('", "', self::AVAILABLE_KEYS)));
        }

        if (isset($config['resource'])) {
            if (isset($config['channel']) || isset($config['pattern'])) {
                throw new \InvalidArgumentException(sprintf('The routing file "%s" must not specify both the "resource" key and the "pattern" key for "%s". Choose between an import and a route definition.', $path, $name));
            }
        } else {
            if (isset($config['type'])) {
                throw new \InvalidArgumentException(sprintf('The "type" key for the route definition "%s" in "%s" is unsupported. It is only available for imports in combination with the "resource" key.', $name, $path));
            }

            if (isset($config['pattern']) && isset($config['channel'])) {
                throw new \InvalidArgumentException(sprintf('The route "%s" in file "%s" must not specify both the "channel" key and the "pattern" key.', $name, $path));
            } elseif (isset($config['channel'])) {
                trigger_deprecation('gos/pubsub-router-bundle', '2.4', 'Setting the "channel" key for the route "%s" in file "%s" is deprecated and will not be supported in 3.0, use the "pattern" key instead.', $name, $path);
            } elseif (!isset($config['pattern'])) {
                throw new \InvalidArgumentException(sprintf('You must define a "pattern" for the route "%s" in file "%s".', $name, $path));
            }

            if (isset($config['callback']) && isset($config['handler'])) {
                throw new \InvalidArgumentException(sprintf('The route "%s" in file "%s" must not specify both the "handler" key and the "callback" key.', $name, $path));
            } elseif (isset($config['handler'])) {
                trigger_deprecation('gos/pubsub-router-bundle', '2.4', 'Setting the "handler" key for the route "%s" in file "%s" is deprecated and will not be supported in 3.0, use the "callback" key instead.', $name, $path);
            } elseif (!isset($config['callback'])) {
                throw new \InvalidArgumentException(sprintf('You must define a "callback" for the route "%s" in file "%s".', $name, $path));
            }
        }
    }

    /**
     * @param mixed $resource
     */
    protected function doSupports($resource, string $type = null): bool
    {
        return \is_string($resource)
            && \in_array(pathinfo($resource, PATHINFO_EXTENSION), ['yml', 'yaml'], true)
            && (!$type || 'yaml' === $type);
    }
}

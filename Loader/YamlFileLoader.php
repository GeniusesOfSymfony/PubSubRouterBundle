<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class YamlFileLoader extends FileLoader
{
    /**
     * @var array
     */
    private static $availableKeys = array(
        'channel', 'handler', 'requirements',
    );

    /**
     * @var YamlParser
     */
    private $yamlParser;

    /**
     * {@inheritdoc}
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null)
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
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $path), 0, $e);
        }

        $config = $this->yamlParser->parse(file_get_contents($path));

        $routeCollection = new RouteCollection();
        $routeCollection->addResource(new FileResource($path));

        // empty file
        if (null === $config) {
            return $routeCollection;
        }

        // not an array
        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        foreach ($config as $routeName => $routeConfig) {
            $this->validate($routeConfig, $routeName, $path);

            $routeCollection->add(
                $routeName,
                new Route(
                    $routeConfig['channel'],
                    $routeConfig['handler'],
                    isset($routeConfig['requirements']) ? $routeConfig['requirements'] : []
                )
            );
        }

        return $routeCollection;
    }

    /**
     * @param array  $config
     * @param string $name
     * @param string $path
     */
    protected function validate($config, $name, $path)
    {
        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The definition of "%s" in "%s" must be a YAML array.', $name, $path));
        }

        if ($extraKeys = array_diff(array_keys($config), self::$availableKeys)) {
            throw new \InvalidArgumentException(sprintf(
                'The routing file "%s" contains unsupported keys for "%s": "%s". Expected one of: "%s".',
                $path, $name, implode('", "', $extraKeys), implode('", "', self::$availableKeys)
            ));
        }

        if (!isset($config['channel'])) {
            throw new \InvalidArgumentException(sprintf('You must define a "channel" for the route "%s" in file "%s".', $name, $path));
        }

        if (!isset($config['handler'])) {
            throw new \InvalidArgumentException(sprintf('You must define a "handler" for the route "%s" in file "%s".', $name, $path));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource)
            && \in_array(pathinfo($resource, PATHINFO_EXTENSION), ['yml', 'yaml'], true)
            && (!$type || 'yaml' === $type);
    }
}

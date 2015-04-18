<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Yaml\Parser;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class YamlFileLoader extends AbstractRouteLoader
{
    /**
     * @var array
     */
    private static $availableKeys = array(
        'channel', 'handler', 'requirements',
    );

    /**
     * @var Parser
     */
    private $yamlParser;

    /**
     * @param string $resource
     * @param null   $type
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        $resource = parent::load($resource, $type);

        if (null === $this->yamlParser) {
            $this->yamlParser = new Parser();
        }

        $config = $this->yamlParser->parse(file_get_contents($resource));

        $routeCollection = new RouteCollection();

        if (!is_array($config)) {
            throw new \InvalidArgumentException('Invalid configuration');
        }

        foreach ($config as $routeName => $routeConfig) {
            $this->validate($routeConfig, $routeName, $resource);

            $handler = $routeConfig['handler'];

            $routeCollection->add($routeName, new Route(
                $routeConfig['channel'],
                $handler['callback'],
                isset($handler['args']) ? $handler['args'] : [],
                isset($routeConfig['requirements']) ? $routeConfig['requirements'] : []
            ));
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
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'yaml' === $type);
    }
}

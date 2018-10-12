<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Generator\GeneratorInterface;
use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoader;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Matcher\MatcherInterface;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class Router implements RouterInterface
{
    /**
     * @var MatcherInterface|null
     */
    protected $matcher;

    /**
     * @var GeneratorInterface|null
     */
    protected $generator;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var RouteCollection|null
     */
    protected $collection;

    /**
     * @var array
     */
    protected $resources = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ConfigCacheFactoryInterface|null
     */
    private $configCacheFactory;

    /**
     * @param string          $name
     * @param LoaderInterface $loader
     * @param array           $resources
     * @param array           $options
     */
    public function __construct($name, LoaderInterface $loader, $resources, array $options = array())
    {
        $this->name = $name;
        $this->loader = $loader;
        $this->resource = $resource;
        $this->setOptions($options);
    }

    /**
     * Sets options.
     *
     * Available options:
     *
     *   * cache_dir:              The cache directory (or null to disable caching)
     *   * debug:                  Whether to enable debugging or not (false by default)
     *   * generator_class:        The name of a GeneratorInterface implementation
     *   * generator_base_class:   The base class for the dumped generator class
     *   * generator_cache_class:  The class name for the dumped generator class
     *   * generator_dumper_class: The name of a GeneratorDumperInterface implementation
     *   * matcher_class:          The name of a MatcherInterface implementation
     *   * matcher_base_class:     The base class for the dumped matcher class
     *   * matcher_dumper_class:   The class name for the dumped matcher class
     *   * matcher_cache_class:    The name of a MatcherDumperInterface implementation
     *   * resource_type:          Type hint for the main resource (optional)
     *
     * @param array $options An array of options
     *
     * @throws \InvalidArgumentException When unsupported option is provided
     */
    public function setOptions(array $options)
    {
        $this->options = [
            'cache_dir' => null,
            'debug' => false,
            'generator_class' => Generator::class,
            'generator_base_class' => Generator::class,
            'generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper',
            'generator_cache_class' => 'Project'.ucfirst(strtolower($this->name)).'Generator',
            'matcher_class' => Matcher::class,
            'matcher_base_class' => Matcher::class,
            'matcher_dumper_class' => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper',
            'matcher_cache_class' => 'Project'.ucfirst(strtolower($this->name)).'Matcher',
            'resource_type' => null,
        ];

        // check option names and live merge, if errors are encountered Exception will be thrown
        $invalid = [];

        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                $invalid[] = $key;
            }
        }

        if ($invalid) {
            throw new \InvalidArgumentException(
                sprintf('The Router does not support the following options: "%s".', implode('", "', $invalid))
            );
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @throws \InvalidArgumentException
     */
    public function setOption($key, $value)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        $this->options[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function getOption($key)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        return $this->options[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        if (null === $this->collection) {
            foreach ($this->resources as $resource) {
                if (null === $this->collection) {
                    $this->collection = $this->loader->load($resource, $this->options['resource_type']);
                } else {
                    $this->collection->addCollection($this->loader->load($resource, $this->options['resource_type']));
                }
            }
        }

        return $this->collection;
    }

    /**
     * Sets the ConfigCache factory to use.
     */
    public function setConfigCacheFactory(ConfigCacheFactoryInterface $configCacheFactory)
    {
        $this->configCacheFactory = $configCacheFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($routeName, array $parameters = [])
    {
        return $this->getGenerator()->generate($routeName, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function match($channel, $tokenSeparator = null)
    {
        $this->matcher->setCollection($this->collection);

        if (null === $tokenSeparator && null !== $this->context) {
            $tokenSeparator = $this->context->getTokenSeparator();
        }

        return $this->matcher->match($channel, $tokenSeparator);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the Generator instance associated with this Router.
     *
     * @return GeneratorInterface
     */
    public function getGenerator()
    {
        if (null !== $this->generator) {
            return $this->generator;
        }

        if (null === $this->options['cache_dir'] || null === $this->options['generator_cache_class']) {
            $this->generator = new $this->options['generator_class']($this->getCollection());
        } else {
            $cache = $this->getConfigCacheFactory()->cache(
                $this->options['cache_dir'].'/'.$this->options['generator_cache_class'].'.php',
                function (ConfigCacheInterface $cache) {
                    $dumper = $this->getGeneratorDumperInstance();

                    $options = [
                        'class' => $this->options['generator_cache_class'],
                        'base_class' => $this->options['generator_base_class'],
                    ];

                    $cache->write($dumper->dump($options), $this->getCollection()->getResources());
                }
            );

            if (!class_exists($this->options['generator_cache_class'], false)) {
                require_once $cache->getPath();
            }

            $this->generator = new $this->options['generator_cache_class']();
        }

        return $this->generator;
    }

    /**
     * @return GeneratorDumperInterface
     */
    protected function getGeneratorDumperInstance()
    {
        return new $this->options['generator_dumper_class']($this->getRouteCollection());
    }

    /**
     * Provides the ConfigCache factory implementation, falling back to a default implementation if necessary.
     *
     * @return ConfigCacheFactoryInterface $configCacheFactory
     */
    private function getConfigCacheFactory()
    {
        if (null === $this->configCacheFactory) {
            $this->configCacheFactory = new ConfigCacheFactory($this->options['debug']);
        }

        return $this->configCacheFactory;
    }
}

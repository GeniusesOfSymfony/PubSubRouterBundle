<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

use Gos\Bundle\PubSubRouterBundle\Generator\Dumper\GeneratorDumperInterface;
use Gos\Bundle\PubSubRouterBundle\Generator\Dumper\PhpGeneratorDumper;
use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Generator\GeneratorInterface;
use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\MatcherDumperInterface;
use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\PhpMatcherDumper;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Matcher\MatcherInterface;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class Router implements RouterInterface, WarmableInterface
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
    public function __construct(string $name, LoaderInterface $loader, array $resources, array $options = [])
    {
        $this->name = $name;
        $this->loader = $loader;
        $this->resources = $resources;
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
    public function setOptions(array $options): void
    {
        $this->options = [
            'cache_dir' => null,
            'debug' => false,
            'generator_class' => Generator::class,
            'generator_base_class' => Generator::class,
            'generator_dumper_class' => PhpGeneratorDumper::class,
            'generator_cache_class' => 'Project'.ucfirst(strtolower($this->name)).'Generator',
            'matcher_class' => Matcher::class,
            'matcher_base_class' => Matcher::class,
            'matcher_dumper_class' => PhpMatcherDumper::class,
            'matcher_cache_class' => 'Project'.ucfirst(strtolower($this->name)).'Matcher',
            'resource_type' => null,
        ];

        // check option names and live merge, if errors are encountered Exception will be thrown
        $invalid = [];

        foreach ($options as $key => $value) {
            if (\array_key_exists($key, $this->options)) {
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
     * @throws \InvalidArgumentException
     */
    public function setOption(string $key, $value): void
    {
        if (!\array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        $this->options[$key] = $value;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getOption(string $key)
    {
        if (!\array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        return $this->options[$key];
    }

    public function getCollection(): RouteCollection
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

    public function warmUp($cacheDir)
    {
        $currentDir = $this->getOption('cache_dir');

        // force cache generation
        $this->setOption('cache_dir', $cacheDir);
        $this->getMatcher();
        $this->getGenerator();

        $this->setOption('cache_dir', $currentDir);
    }

    public function setConfigCacheFactory(ConfigCacheFactoryInterface $configCacheFactory): void
    {
        $this->configCacheFactory = $configCacheFactory;
    }

    public function generate(string $routeName, array $parameters = []): string
    {
        return $this->getGenerator()->generate($routeName, $parameters);
    }

    public function match(string $channel): array
    {
        return $this->getMatcher()->match($channel);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGenerator(): GeneratorInterface
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

    public function getMatcher(): MatcherInterface
    {
        if (null !== $this->matcher) {
            return $this->matcher;
        }

        if (null === $this->options['cache_dir'] || null === $this->options['matcher_cache_class']) {
            $this->matcher = new $this->options['matcher_class']($this->getCollection());

            return $this->matcher;
        }

        $cache = $this->getConfigCacheFactory()->cache($this->options['cache_dir'].'/'.$this->options['matcher_cache_class'].'.php',
            function (ConfigCacheInterface $cache) {
                $dumper = $this->getMatcherDumperInstance();

                $options = [
                    'class' => $this->options['matcher_cache_class'],
                    'base_class' => $this->options['matcher_base_class'],
                ];

                $cache->write($dumper->dump($options), $this->getCollection()->getResources());
            }
        );

        if (!class_exists($this->options['matcher_cache_class'], false)) {
            require_once $cache->getPath();
        }

        return $this->matcher = new $this->options['matcher_cache_class']();
    }

    protected function getGeneratorDumperInstance(): GeneratorDumperInterface
    {
        return new $this->options['generator_dumper_class']($this->getCollection());
    }

    protected function getMatcherDumperInstance(): MatcherDumperInterface
    {
        return new $this->options['matcher_dumper_class']($this->getCollection());
    }

    /**
     * Provides the ConfigCache factory implementation, falling back to a default implementation if necessary.
     */
    private function getConfigCacheFactory(): ConfigCacheFactoryInterface
    {
        if (null === $this->configCacheFactory) {
            $this->configCacheFactory = new ConfigCacheFactory($this->options['debug']);
        }

        return $this->configCacheFactory;
    }
}

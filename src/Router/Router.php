<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

use Gos\Bundle\PubSubRouterBundle\Generator\CompiledGenerator;
use Gos\Bundle\PubSubRouterBundle\Generator\Dumper\CompiledGeneratorDumper;
use Gos\Bundle\PubSubRouterBundle\Generator\Dumper\GeneratorDumperInterface;
use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Generator\GeneratorInterface;
use Gos\Bundle\PubSubRouterBundle\Matcher\CompiledMatcher;
use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\CompiledMatcherDumper;
use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\MatcherDumperInterface;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Matcher\MatcherInterface;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 * @final
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
     * @var array{
     *     cache_dir: string|null,
     *     debug: bool,
     *     generator_class: class-string<GeneratorInterface>,
     *     generator_base_class: class-string<GeneratorInterface>,
     *     generator_cache_class: non-empty-string|null,
     *     generator_dumper_class: class-string<GeneratorDumperInterface>,
     *     matcher_class: class-string<MatcherInterface>,
     *     matcher_base_class: class-string<MatcherInterface>,
     *     matcher_cache_class: non-empty-string|null,
     *     matcher_dumper_class: class-string<MatcherDumperInterface>,
     *     resource_type: string|null,
     * }
     */
    protected $options;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ConfigCacheFactoryInterface|null
     */
    private $configCacheFactory;

    /**
     * @var array<string, array>|null
     */
    private static $cache = [];

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
     *   * matcher_cache_class:    The class name for the dumped matcher class
     *   * matcher_dumper_class:   The name of a MatcherDumperInterface implementation
     *   * resource_type:          Type hint for the main resource (optional)
     *
     * @param array $options An array of options
     *
     * @throws \InvalidArgumentException when an unsupported option is provided
     */
    public function setOptions(array $options): void
    {
        $this->options = [
            'cache_dir' => null,
            'debug' => false,
            'generator_class' => Generator::class,
            'generator_base_class' => Generator::class,
            'generator_dumper_class' => CompiledGeneratorDumper::class,
            'generator_cache_class' => 'Project'.ucfirst(strtolower($this->name)).'Generator',
            'matcher_class' => Matcher::class,
            'matcher_base_class' => Matcher::class,
            'matcher_dumper_class' => CompiledMatcherDumper::class,
            'matcher_cache_class' => 'Project'.ucfirst(strtolower($this->name)).'Matcher',
            'resource_type' => null,
        ];

        // check option names and live merge, if errors are encountered Exception will be thrown
        $invalid = [];

        foreach ($options as $key => $value) {
            $this->checkDeprecatedOption($key);

            if (\array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                $invalid[] = $key;
            }
        }

        if ($invalid) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the following options: "%s".', implode('", "', $invalid)));
        }
    }

    /**
     * @param mixed $value
     *
     * @throws \InvalidArgumentException when an unsupported option is provided
     */
    public function setOption(string $key, $value): void
    {
        if (!\array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        $this->checkDeprecatedOption($key);

        $this->options[$key] = $value;
    }

    /**
     * @return mixed
     *
     * @throws \InvalidArgumentException when an unsupported option is provided
     */
    public function getOption(string $key)
    {
        if (!\array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        $this->checkDeprecatedOption($key);

        return $this->options[$key];
    }

    public function getCollection(): RouteCollection
    {
        if (null === $this->collection) {
            $this->collection = new RouteCollection();

            foreach ($this->resources as $resource) {
                if (\is_array($resource)) {
                    $type = isset($resource['type']) && null !== $resource ? $resource['type'] : $this->options['resource_type'];

                    $this->collection->addCollection($this->loader->load($resource['resource'], $type));
                } else {
                    $this->collection->addCollection($this->loader->load($resource, $this->options['resource_type']));
                }
            }
        }

        return $this->collection;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     *
     * @return string[] A list of classes to preload on PHP 7.4+
     */
    public function warmUp($cacheDir)
    {
        $currentDir = $this->getOption('cache_dir');

        // force cache generation
        $this->setOption('cache_dir', $cacheDir);
        $this->getMatcher();
        $this->getGenerator();

        $this->setOption('cache_dir', $currentDir);

        return [
            $this->getOption('generator_class'),
            $this->getOption('matcher_class'),
        ];
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

        $compiled = is_a($this->options['generator_class'], CompiledGenerator::class, true) && Generator::class === $this->options['generator_base_class'] && is_a($this->options['generator_dumper_class'], CompiledGeneratorDumper::class, true);

        if (null === $this->options['cache_dir'] || null === $this->options['generator_cache_class']) {
            $routes = $this->getCollection();

            if ($compiled) {
                $routes = (new CompiledGeneratorDumper($routes))->getCompiledRoutes();
            }

            return $this->generator = new $this->options['generator_class']($routes);
        }

        $cache = $this->getConfigCacheFactory()->cache($this->options['cache_dir'].'/'.$this->options['generator_cache_class'].'.php',
            function (ConfigCacheInterface $cache): void {
                $dumper = $this->getGeneratorDumperInstance();

                $options = [
                    'class' => $this->options['generator_cache_class'],
                    'base_class' => $this->options['generator_base_class'],
                ];

                $cache->write($dumper->dump($options), $this->getCollection()->getResources());
            }
        );

        if ($compiled) {
            return $this->generator = new $this->options['generator_class'](self::getCompiledRoutes($cache->getPath()));
        }

        if (!class_exists($this->options['generator_cache_class'], false)) {
            require_once $cache->getPath();
        }

        return $this->generator = new $this->options['generator_cache_class']();
    }

    public function getMatcher(): MatcherInterface
    {
        if (null !== $this->matcher) {
            return $this->matcher;
        }

        $compiled = is_a($this->options['matcher_class'], CompiledMatcher::class, true) && Matcher::class === $this->options['matcher_base_class'] && is_a($this->options['matcher_dumper_class'], CompiledMatcherDumper::class, true);

        if (null === $this->options['cache_dir'] || null === $this->options['matcher_cache_class']) {
            $routes = $this->getCollection();

            if ($compiled) {
                $routes = (new CompiledMatcherDumper($routes))->getCompiledRoutes();
            }

            return $this->matcher = new $this->options['matcher_class']($routes);
        }

        $cache = $this->getConfigCacheFactory()->cache($this->options['cache_dir'].'/'.$this->options['matcher_cache_class'].'.php',
            function (ConfigCacheInterface $cache): void {
                $dumper = $this->getMatcherDumperInstance();

                $options = [
                    'class' => $this->options['matcher_cache_class'],
                    'base_class' => $this->options['matcher_base_class'],
                ];

                $cache->write($dumper->dump($options), $this->getCollection()->getResources());
            }
        );

        if ($compiled) {
            return $this->matcher = new $this->options['matcher_class'](self::getCompiledRoutes($cache->getPath()));
        }

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

    private function checkDeprecatedOption(string $key): void
    {
        switch ($key) {
            case 'generator_base_class':
            case 'generator_cache_class':
            case 'matcher_base_class':
            case 'matcher_cache_class':
                trigger_deprecation('gos/pubsub-router-bundle', '2.4', sprintf('Option "%s" given to router %s is deprecated.', $key, static::class));
        }
    }

    private static function getCompiledRoutes(string $path): array
    {
        if ([] === self::$cache && \function_exists('opcache_invalidate') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN) && (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) || filter_var(ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOLEAN))) {
            self::$cache = null;
        }

        if (null === self::$cache) {
            return require $path;
        }

        if (isset(self::$cache[$path])) {
            return self::$cache[$path];
        }

        return self::$cache[$path] = require $path;
    }
}

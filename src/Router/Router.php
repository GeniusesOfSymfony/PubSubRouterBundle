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
 */
final class Router implements RouterInterface, WarmableInterface
{
    private ?MatcherInterface $matcher = null;
    private ?GeneratorInterface $generator = null;
    private LoaderInterface $loader;
    private ?RouteCollection $collection = null;

    /**
     * @var array<array{resource: string|callable, type: string|null}|string>
     */
    private array $resources;

    private string $name;
    private ?ConfigCacheFactoryInterface $configCacheFactory = null;

    /**
     * @var array<string, mixed>
     */
    private array $options = [];

    /**
     * @var array<string, array>|null
     */
    private static ?array $cache = [];

    /**
     * @param array<array{resource: string|callable, type: string|null}|string> $resources
     * @param array<string, mixed>                                              $options
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
     *   * generator_dumper_class: The name of a GeneratorDumperInterface implementation
     *   * matcher_class:          The name of a MatcherInterface implementation
     *   * matcher_dumper_class:   The name of a MatcherDumperInterface implementation
     *   * resource_type:          Type hint for the main resource (optional)
     *
     * @param array<string, mixed> $options An array of options
     *
     * @throws \InvalidArgumentException when an unsupported option is provided
     */
    public function setOptions(array $options): void
    {
        $this->options = [
            'cache_dir' => null,
            'debug' => false,
            'generator_class' => Generator::class,
            'generator_dumper_class' => CompiledGeneratorDumper::class,
            'matcher_class' => Matcher::class,
            'matcher_dumper_class' => CompiledMatcherDumper::class,
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
            throw new \InvalidArgumentException(sprintf('The Router does not support the following options: "%s".', implode('", "', $invalid)));
        }
    }

    /**
     * @throws \InvalidArgumentException when an unsupported option is provided
     */
    public function setOption(string $key, mixed $value): void
    {
        if (!\array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        $this->options[$key] = $value;
    }

    /**
     * @throws \InvalidArgumentException when an unsupported option is provided
     */
    public function getOption(string $key): mixed
    {
        if (!\array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        return $this->options[$key];
    }

    public function getCollection(): RouteCollection
    {
        if (null === $this->collection) {
            $this->collection = new RouteCollection();

            foreach ($this->resources as $resource) {
                if (\is_array($resource)) {
                    $type = $resource['type'] ?? $this->options['resource_type'];

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
    public function warmUp(string $cacheDir): array
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

        if (null === $this->options['cache_dir']) {
            $routes = $this->getCollection();
            $compiled = is_a($this->options['generator_class'], CompiledGenerator::class, true);

            if ($compiled) {
                $routes = (new CompiledGeneratorDumper($routes))->getCompiledRoutes();
            }

            $this->generator = new $this->options['generator_class']($routes);
        } else {
            $cache = $this->getConfigCacheFactory()->cache($this->options['cache_dir'].'/'.strtolower($this->name).'_pubsub_router_generating_routes.php',
                function (ConfigCacheInterface $cache): void {
                    $dumper = $this->getGeneratorDumperInstance();

                    $cache->write($dumper->dump(), $this->getCollection()->getResources());
                }
            );

            $this->generator = new $this->options['generator_class'](self::getCompiledRoutes($cache->getPath()));
        }

        return $this->generator;
    }

    public function getMatcher(): MatcherInterface
    {
        if (null !== $this->matcher) {
            return $this->matcher;
        }

        if (null === $this->options['cache_dir']) {
            $routes = $this->getCollection();
            $compiled = is_a($this->options['matcher_class'], CompiledMatcher::class, true);

            if ($compiled) {
                $routes = (new CompiledMatcherDumper($routes))->getCompiledRoutes();
            }

            return $this->matcher = new $this->options['matcher_class']($routes);
        }

        $cache = $this->getConfigCacheFactory()->cache($this->options['cache_dir'].'/'.strtolower($this->name).'_pubsub_router_matching_routes.php',
            function (ConfigCacheInterface $cache): void {
                $dumper = $this->getMatcherDumperInstance();

                $cache->write($dumper->dump(), $this->getCollection()->getResources());
            }
        );

        return $this->matcher = new $this->options['matcher_class'](self::getCompiledRoutes($cache->getPath()));
    }

    private function getGeneratorDumperInstance(): GeneratorDumperInterface
    {
        return new $this->options['generator_dumper_class']($this->getCollection());
    }

    private function getMatcherDumperInstance(): MatcherDumperInterface
    {
        return new $this->options['matcher_dumper_class']($this->getCollection());
    }

    private function getConfigCacheFactory(): ConfigCacheFactoryInterface
    {
        if (null === $this->configCacheFactory) {
            $this->configCacheFactory = new ConfigCacheFactory($this->options['debug']);
        }

        return $this->configCacheFactory;
    }

    private static function getCompiledRoutes(string $path): array
    {
        if ([] === self::$cache && \function_exists('opcache_invalidate') && filter_var(ini_get('opcache.enable'), \FILTER_VALIDATE_BOOLEAN) && (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) || filter_var(ini_get('opcache.enable_cli'), \FILTER_VALIDATE_BOOLEAN))) {
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

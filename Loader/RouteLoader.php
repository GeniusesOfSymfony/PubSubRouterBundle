<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class RouteLoader implements CacheWarmerInterface
{
    /** @var LoaderInterface[] */
    protected $loaders;

    /** @var string[] */
    protected $resources;

    /** @var RouteCollection */
    protected $routeCollection;

    /** @var  string */
    protected $cacheDir;

    /** @var  Bool */
    protected $debug;

    /** @var  ConfigCache */
    protected $cache;

    /** @var  string */
    protected $fileName;

    const CACHE_FILE_NAME = 'gosPubSubRouter.php';

    /**
     * @param RouteCollection $routeCollection
     * @param string          $cacheDir
     * @param bool            $debug
     */
    public function __construct(
        RouteCollection $routeCollection,
        $cacheDir,
        $debug
    ) {
        $this->routeCollection = $routeCollection;
        $this->cacheDir = $cacheDir;
        $this->debug = $debug;
        $this->fileName = $this->cacheDir . '/'. self::CACHE_FILE_NAME;
        $this->cache = new ConfigCache($this->fileName, $this->debug);
    }

    /**
     * @param $resource
     */
    public function addResource($resource)
    {
        $this->resources[] = $resource;
    }

    /**
     * @param LoaderInterface $loader
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    public function load()
    {
        if (!$this->cache->isFresh()) {
            $loaderResolver = new LoaderResolver($this->loaders);
            $delegatingLoader = new DelegatingLoader($loaderResolver);

            foreach ($this->resources as $resource) {
                $this->routeCollection->addCollection($delegatingLoader->load($resource));
            }

            $this->cache->write($this->generateContent());
        } else {
            $collection =  require_once $this->fileName;
            $this->routeCollection->addCollection($collection);
        }
    }

    /**
     * @return string
     */
    protected function generateContent()
    {
        $content = <<<PHP
<?php
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

PHP;
        $content .= PHP_EOL . '$collection = new RouteCollection();' . PHP_EOL;

        foreach ($this->routeCollection as $routeName => $route) {
            $routeArgs = [
                var_export($route->getPattern(), true),
                var_export($route->getCallback(), true),
                var_export($route->getArgs(), true),
                var_export($route->getRequirements(), true),
            ];

            $content .= PHP_EOL . '$collection->add(' . var_export($routeName, true) . ', new Route(' . implode(', ', $routeArgs) . '));' . PHP_EOL;
        }

        $content .= PHP_EOL . 'return $collection;';

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->cache->write($this->generateContent());
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class Router implements RouterInterface, WarmableInterface
{
    /**
     * @var RouteCollection
     */
    protected $collection;

    /**
     * @var RouterContext
     */
    protected $context;

    /**
     * @var string[]
     */
    protected $resources;

    /**
     * @var LoaderInterface[]
     */
    protected $loaders;

    /**
     * @var bool
     */
    protected $loaded;

    public function __construct()
    {
        $this->collection = new RouteCollection();
        $this->resources = array();
        $this->loaded = false;
    }

    /**
     * @param LoaderInterface $loader
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * @param RouterContext $context
     */
    public function setContext(RouterContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return RouterContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $resource
     */
    public function addResource($resource)
    {
        $this->resources[] = $resource;
    }

    public function loadRoute()
    {
        $loaderResolver = new LoaderResolver($this->loaders);
        $delegatingLoader = new DelegatingLoader($loaderResolver);

        foreach($this->resources as $resource){
            $this->collection->addCollection($delegatingLoader->load($resource));
        }

        $this->loaded = true;
    }

    /**
     * @return bool
     */
    public function isLoaded()
    {
        return true === $this->loaded;
    }

    /**
     * @return RouteCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $this->loadRoute();

        //do stuff with collection
    }
}
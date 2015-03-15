<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Matcher\MatcherInterface;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

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
     * @var MatcherInterface
     */
    protected $matcher;

    /**
     * @var bool
     */
    protected $loaded;

    public function __construct()
    {
        $this->collection = new RouteCollection();
        $this->resources = array();
        $this->loaded = false;
        $this->matcher = new Matcher();
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
     * @param string      $channel
     * @param null|string $tokenSeparator
     */
    public function match($channel, $tokenSeparator = null)
    {
        if (!$this->isLoaded()) {
            throw new \LogicException('You must load router before');
        }

        if (null === $tokenSeparator && null !== $this->context) {
            $tokenSeparator = $this->context->getTokenSeparator();
        }

        return $this->matcher->match($channel, $tokenSeparator);
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

        foreach ($this->resources as $resource) {
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

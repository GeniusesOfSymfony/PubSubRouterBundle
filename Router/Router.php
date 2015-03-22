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
     * {@inheritdoc}
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RouterContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function match($channel, $tokenSeparator = null)
    {
        if (!$this->isLoaded()) {
            throw new \LogicException('You must load router before');
        }

        if (null === $tokenSeparator && null !== $this->context) {
            $tokenSeparator = $this->context->getTokenSeparator();
        }

        return $this->matcher->match($channel,  $this->collection, $tokenSeparator);
    }

    /**
     * {@inheritdoc}
     */
    public function addResource($resource)
    {
        $this->resources[] = $resource;
    }

    /**
     * {@inheritdoc}
     */
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
     * {@inheritdoc}
     */
    public function isLoaded()
    {
        return true === $this->loaded;
    }

    /**
     * {@inheritdoc}
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

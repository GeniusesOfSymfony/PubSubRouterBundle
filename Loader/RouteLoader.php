<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Doctrine\Common\Cache\Cache;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;

class RouteLoader
{
    /** @var LoaderInterface[] */
    protected $loaders;

    /** @var string[] */
    protected $resources;

    /** @var Cache */
    protected $cache;

    /** @var  RouteCollection */
    protected $collection;

    /** @var string */
    protected $type;

    /**
     * @param RouteCollection $collection
     * @param Cache           $cache
     * @param string          $type
     */
    public function __construct(RouteCollection $collection, Cache $cache, $type)
    {
        $this->resources = [];
        $this->loaders = [];
        $this->cache = $cache;
        $this->type = $type;
        $this->collection = $collection;
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

    /**
     * @param RouteCollection $collection
     *
     * @return RouteCollection
     */
    public function load()
    {
        if ($cachedCollection = $this->cache->fetch('collection.' . $this->type)) {
            $this->collection->addCollection($cachedCollection);

            return $this->collection;
        }

        $loaderResolver = new LoaderResolver($this->loaders);
        $delegatingLoader = new DelegatingLoader($loaderResolver);

        foreach ($this->resources as $resource) {
            $this->collection->addCollection($delegatingLoader->load($resource));
        }

        $this->cache->save('collection.' . $this->type, $this->collection);

        return $this->collection;
    }

    /**
     * @return RouteCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}

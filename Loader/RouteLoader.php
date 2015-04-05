<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

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

    public function __construct()
    {
        $this->resources = [];
        $this->loaders = [];
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
    public function load(RouteCollection $collection)
    {
        $loaderResolver = new LoaderResolver($this->loaders);
        $delegatingLoader = new DelegatingLoader($loaderResolver);

        foreach ($this->resources as $resource) {
            $collection->addCollection($delegatingLoader->load($resource));
        }

        return $collection;
    }
}

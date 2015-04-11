<?php

namespace Gos\Bundle\PubSubRouterBundle\Cache;

use Doctrine\Common\Cache\Cache;
use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoader;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class CacheWarmer implements CacheWarmerInterface
{
    /** @var  ContainerInterface */
    protected $container;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param Cache              $cache
     * @param ContainerInterface $container
     */
    public function __construct(Cache $cache, ContainerInterface $container)
    {
        $this->cache = $cache;
        $this->container = $container;
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
        $registeredRouter = $this->container->getParameter('gos_pubsub_registered_routers');

        foreach ($registeredRouter as $routerType) {

            /** @var RouteCollection $collection */
            $collection = $this->container->get('gos_pubsub_router.collection.' . $routerType);

            /** @var RouteLoader $loader */
            $loader = $this->container->get('gos_pubsub_router.loader.' . $routerType);

            $loader->load($collection); //trigger cache on route collection
        }
    }
}

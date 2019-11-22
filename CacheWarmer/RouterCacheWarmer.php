<?php

namespace Gos\Bundle\PubSubRouterBundle\CacheWarmer;

use Gos\Bundle\PubSubRouterBundle\Router\RouterRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

class RouterCacheWarmer implements CacheWarmerInterface, CompatibilityServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        // As this cache warmer is optional, dependencies should be lazy-loaded, that's why a container should be injected.
        $this->container = $container;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        /** @var RouterRegistry $registry */
        $registry = $this->container->get('gos_pubsub_router.router_registry');

        foreach ($registry->getRouters() as $router) {
            if ($router instanceof WarmableInterface) {
                $router->warmUp($cacheDir);
            }
        }
    }

    public function isOptional()
    {
        return true;
    }

    public static function getSubscribedServices()
    {
        return [
            'gos_pubsub_router.router_registry' => RouterRegistry::class,
        ];
    }
}

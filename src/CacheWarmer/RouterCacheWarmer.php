<?php

namespace Gos\Bundle\PubSubRouterBundle\CacheWarmer;

use Gos\Bundle\PubSubRouterBundle\Router\RouterRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final class RouterCacheWarmer implements CacheWarmerInterface, ServiceSubscriberInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     *
     * @return string[] A list of classes to preload on PHP 7.4+
     * @phpstan-return class-string[]
     */
    public function warmUp(string $cacheDir): array
    {
        /** @var RouterRegistry $registry */
        $registry = $this->container->get('gos_pubsub_router.router_registry');

        $classes = [];

        foreach ($registry->getRouters() as $router) {
            if ($router instanceof WarmableInterface) {
                $classes = array_merge(
                    $classes,
                    (array) $router->warmUp($cacheDir)
                );
            }
        }

        return array_unique($classes);
    }

    public function isOptional(): bool
    {
        return true;
    }

    /**
     * @phpstan-return array<string, string|class-string>
     */
    public static function getSubscribedServices(): array
    {
        return [
            'gos_pubsub_router.router_registry' => RouterRegistry::class,
        ];
    }
}

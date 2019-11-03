# Changelog

## 1.1.0 (2019-08-11)

- Adjust configuration for changes introduced in Symfony 4.2
- Restore `Serializable` interface implementation to `Gos\Bundle\PubSubRouterBundle\Router\Route` class
- Adjust serialization implementation in `Gos\Bundle\PubSubRouterBundle\Router\CompiledRoute` and  `Gos\Bundle\PubSubRouterBundle\Router\Route` to use the new PHP 7.4 syntax (with backward compatibility)
- Deprecated the `gos_pubsub_router.router.registry` service ID in favor of `gos_pubsub_router.router_registry` and `gos_pubsub_router.router.cache_warmer` in favor of `gos_pubsub_router.cache_warmer.router`; the former IDs are now aliases and will be removed in GosPubSubRouterBundle 2.0
    - The bundle prefixes all created router services with `gos_pubsub_router.router.`, therefore this change moves non-router services outside this "reserved" service namespace
- Disallow routers to be created with the names "registry" or "cache_warmer" due to conflicts with bundle services
# Upgrade from 2.x to 3.0

## Changes

- The minimum supported PHP version is now 7.4
- Made all `@final` annotated classes final

## Removals

- Removed `Gos\Bundle\PubSubRouterBundle\Exception\RouterException`, all bundle exceptions now implement `Gos\Bundle\PubSubRouterBundle\Exception\PubSubRouterException`
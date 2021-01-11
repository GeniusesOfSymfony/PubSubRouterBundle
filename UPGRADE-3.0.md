# Upgrade from 2.x to 3.0

## Changes

- The minimum supported PHP version is now 7.4
- Made all `@final` annotated classes final

## Removals

- Removed `Gos\Bundle\PubSubRouterBundle\Exception\RouterException`, all bundle exceptions now implement `Gos\Bundle\PubSubRouterBundle\Exception\PubSubRouterException`
- Removed `Gos\Bundle\PubSubRouterBundle\Generator\Dumper\PhpGeneratorDumper` and `Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\PhpMatcherDumper` in favor of the compiled dumpers
- Removed the "handler" key used to configure a route's callback in the YAML file loader, use the "callback" key instead

# Upgrade from 2.x to 3.0

## Changes

- The minimum supported PHP version is now 8.0.2
- The minimum supported Symfony version is now 5.2
- Made all `@final` annotated classes final
- The `symfony/yaml` package is now an optional dependency, if you are using it ensure your application is installing the package
- Deprecated public access to the `gos_pubsub_router.router_registry` service, use dependency injection when using the service

## Removals

- Removed `Gos\Bundle\PubSubRouterBundle\Exception\RouterException`, all bundle exceptions now implement `Gos\Bundle\PubSubRouterBundle\Exception\PubSubRouterException`
- Removed `Gos\Bundle\PubSubRouterBundle\Generator\Dumper\PhpGeneratorDumper` and `Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\PhpMatcherDumper` in favor of the compiled dumpers
- Removed the "handler" key used to configure a route's callback in the YAML file loader, use the "callback" key instead
- Removed the "channel" key used to configure a route's pattern in the XML and YAML file loaders, use the "pattern" key instead
- Removed the "router_name" option from the `gos:pubsub-router:debug` command, the name should be passed as the first argument to the command instead

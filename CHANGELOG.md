# Changelog

## 2.6.0 (????-??-??)

- Set correct cache factory on routers

## 2.5.0 (2021-03-18)

- Drop support for Symfony 5.1 (branch is EOL)
- Deprecated the `router_name` argument of the `gos:pubsub-router:debug` command in favor of an argument
- Added support for describing a single route from a router to the `gos:pubsub-router:debug` command
- Added support for multiple output formats in the `gos:pubsub-router:debug` command

## 2.4.0 (2021-01-12)

- Added new compiled cache routers (similar to the same from the `symfony/routing` package)
- Added PHP DSL to configure routers with a shorter PHP syntax
- Deprecated `Gos\Bundle\PubSubRouterBundle\Exception\RouterException`, exception classes should implement `Gos\Bundle\PubSubRouterBundle\Exception\PubSubRouterException` instead
- Deprecated `Gos\Bundle\PubSubRouterBundle\Generator\Dumper\PhpGeneratorDumper` and `Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\PhpMatcherDumper`, use the compiled classes instead
- Deprecated the "handler" key used to configure a route's callback in the YAML file loader, use the "callback" key instead
- Deprecated the "channel" key used to configure a route's pattern in the XML and YAML file loaders, use the "pattern" key instead

## 2.3.0 (2020-11-02)

- Drop support for Symfony 5.0 (branch is EOL)
- Add support for Symfony 5.1 `Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface::warmUp()` changes
- Allow install with PHP 8

## 2.2.0 (2020-02-25)

- Add support for multiple file formats to the router (minus annotations and the PHP-DSL, this brings feature parity with the loaders from the `symfony/routing` package)

## 2.1.0 (2020-02-20)

- Annotated a number of non-final classes as `@final`, they will be made final in 3.0
- [#21](https://github.com/GeniusesOfSymfony/PubSubRouterBundle/issues/21) - Ensure a RouteCollection is returned from the router if there are no resources

## 2.0.0 (2020-01-08)

- Minimum supported Symfony version is 4.4
- Removed deprecated service aliases
- Removed reserved router name list

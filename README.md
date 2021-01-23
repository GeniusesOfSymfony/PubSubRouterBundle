GosPubSubRouterBundle
========================

[![Latest Stable Version](https://poser.pugx.org/gos/pubsub-router-bundle/v/stable)](https://packagist.org/packages/gos/pubsub-router-bundle) [![Latest Unstable Version](https://poser.pugx.org/gos/pubsub-router-bundle/v/unstable)](https://packagist.org/packages/gos/pubsub-router-bundle) [![Total Downloads](https://poser.pugx.org/gos/pubsub-router-bundle/downloads)](https://packagist.org/packages/gos/pubsub-router-bundle) [![License](https://poser.pugx.org/gos/pubsub-router-bundle/license)](https://packagist.org/packages/gos/pubsub-router-bundle) ![Run Tests](https://github.com/GeniusesOfSymfony/PubSubRouterBundle/workflows/Run%20Tests/badge.svg?branch=2.x) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/GeniusesOfSymfony/PubSubRouterBundle/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/GeniusesOfSymfony/PubSubRouterBundle/?branch=2.x) [![Code Coverage](https://scrutinizer-ci.com/g/GeniusesOfSymfony/PubSubRouterBundle/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/GeniusesOfSymfony/PubSubRouterBundle/?branch=2.x)

About
-----
GosPubSubRouterBundle is a Symfony Bundle whose goal is to plug any logic behind pubsub channel. When you use PubSub pattern you will make face to a problem, rely channels with business logic. PubSub router is here to make the junction between channel and business logic.

Support
-------

| Version | Status                       | Symfony Versions   |
| ------- | ---------------------------- | ------------------ |
| 1.x     | Bug Fixes Until July 1, 2021 | 3.4, 4.4, 5.1, 5.2 |
| 2.x     | Actively Supported           | 4.4, 5.1, 5.2      |
| 3.x     | In Development               | 4.4, 5.1, 5.2      |

Features
-------

* [x] Route definition
* [x] Route matching
* [x] Route generator

Installation
------------

Add the bundle to your project using [Composer](https://getcomposer.org/):

```sh
composer require gos/pubsub-router-bundle
```

Once installed, you will need to add the bundle to your project.

If your project is based on Symfony Flex, the bundle should be automatically added to your `config/bundles.php` file:

```php
Gos\Bundle\PubSubRouterBundle\GosPubSubRouterBundle::class => ['all' => true],
```

If your project is based on the Symfony Standard Edition, you will need to add the bundle to your Kernel's `registerBundles` method by editing `app/AppKernel.php`:

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new \Gos\Bundle\PubSubRouterBundle\GosPubSubRouterBundle()
        );
        
        ...
    }
```

Bundle configuration

Below is an example bundle configuration. For projects based on Symfony Flex, this should be stored in `config/packages/gos_pubsub_router.yaml`. For projects based on Symfony Standard Edition, this should be added to `app/config/config.yml`.

```yaml
#Gos PubSub Router
gos_pubsub_router:
    routers:
        websocket: #available from container through gos_pubsub_router.websocket
            resources:
                - @GosNotificationBundle/Resources/config/pubsub/websocket/notification.yml
        redis: #available from container through gos_pubsub_router.redis
            resources:
                - @GosNotificationBundle/Resources/config/pubsub/redis/notification.yml
```

**NOTE** : Each router is insulated. If you have several routers in the same class you will need to inject each router that you need.

Usage
-----

### Routing definition

Example with websocket pubsub

```yaml
user_notification:
    channel: notification/user/{role}/{application}/{user_ref}
    handler: ['Acme\Chat\MessageHandler', 'addPushers']
    requirements:
        role: "editor|admin|client"
        application: "[a-z]+"
        user_ref: "\d+"
```

Example with redis pubsub

```yaml
user_app_notification:
    channel: notification:user:{role}:{application}:{user_ref}
    handler: ['Acme\Chat\MessageHandler', 'addPushers']
    requirements:
        role: "editor|admin|client"
        application: "[a-z-]+-app"
        user_ref: "\d+"
```

**NOTE** : The handler is not typehinted, this allows you to define the handler callback in any way you'd like (such as an array to call a method on a class or a string to call a PHP function or a service from the container).

### Use router

Let's generate a route !

```php
$router = $this->container->get('gos_pubsub_router.websocket');
$channel = $router->generate('user_notification', ['role' => 'admin', 'application' => 'blog-app', 'user_ref' => '123']);

echo $channel // notification/user/admin/blog/123
```

Match your first route !

```php
use Gos\Bundle\PubSubRouterBundle\Request\PubSubRequest;

$channel = 'notification/user/admin/billing-app/639409'; // 'notification/user/admin/billing-app/*' work :)

list($routeName, $route, $attributes) = $router->match($channel);

$request = new PubSubRequest($routeName, $route, $attributes); //Create a request object if you want transport the request data as dependency

//$request->getAttributes()->get('user_ref'); it's a parameterBag

// $router->match($channel);

// $routeName -> 'user_app_notification
// $route -> instance of Gos\Bundle\PubSubRouterBundle\Router\Route
// $attributes -> [ 'role' => 'admin', 'application' => 'billing-app', 'user_ref' => '639409' ]
```

What about mismatch?

```php
use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;

$channel = 'notification/user/admin/billing-app/azerty'; // will miss match

try {
    list($routeName, $route, $attributes) = $router->match($channel);
} catch (ResourceNotFoundException $e) {
    //handle exception
}
```

- If you only need to generate route, typehint against `Gos\Bundle\PubSubRouterBundle\Generator\GeneratorInterface`
- If you only need to match route, typehint against `Gos\Bundle\PubSubRouterBundle\Matcher\MatcherInterface`
- If you need both, typehint against `Gos\Bundle\PubSubRouterBundle\Router\RouterInterface`

### Router CLI

`php bin/console gos:prouter:debug -r websocket` dump all registered routes for websocket router

## License

MIT, See `LICENSE` file in the root of project.



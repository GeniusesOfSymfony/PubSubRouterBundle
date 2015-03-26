Gos PubSub Router Bundle
========================

[![Join the chat at https://gitter.im/GeniusesOfSymfony/PubSubRouterBundle](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/GeniusesOfSymfony/PubSubRouterBundle?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) [![Build Status](https://travis-ci.org/GeniusesOfSymfony/PubSubRouterBundle.svg?branch=master)](https://travis-ci.org/GeniusesOfSymfony/PubSubRouterBundle)

About
--------------
Gos PubSub Router is a Symfony2 Bundle, his goal is to plug any logic behind pubsub channel. When you use PubSub pattern you will make face to a problem, rely channels with business logic. PubSub router is here to make the junction between channel and business logic.

Feature
-------

* [x] Route definition
* [x] Route matching
* [ ] Route generator

Installation
------------

Register the bundle in `app/appKernel.php`

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

```yaml
#Gos PubSub Router
gos_pubsub_router:
    resources: #register all routing definition file
        - @GosNotificationBundle/Resources/config/pubsub/notification.yml
        - ...
```

Usage
-----

### Routing definition

Example with websocket pubsub

```yaml
user_notification:
    channel: notification/user/{role}/{application}/{user_ref}
    handler:
        callback: ['Acme\Chat\MessageHandler', 'addPushers']
        args: [ 'redis', 'websocket' ]
    requirements:
        role:
            pattern: "editor|admin|client"
        application:
            pattern: "[a-z]+"
        user_ref:
            pattern: "\d+"
            wildcard: true
```

Example with redis pubsub

```yaml
user_app_notification:
    channel: notification:user:{role}:{application}:{user_ref}
    handler:
        callback: ['Acme\Chat\MessageHandler', 'addPushers']
        args: [ 'redis', 'websocket' ]
    requirements:
        role:
            pattern: "editor|admin|client"
        application:
            pattern: "[a-z-]+-app"
        user_ref:
            pattern: "\d+"
            wildcard: true
```

**NOTE** : Wildcard match with `*`and `all`

**NOTE 2** : Callback is not type hinted, so you can pass a single string (like a service name) that only depend of your implementation when you retrieve the route ! You are free to choose what you to do !

### Use router

```php
use Gos\Bundle\PubSubRouterBundle\Router\RouterContext;

$router = $this->container->get('gos_pubsub_router.router');
$context = new RouterContext();

// This is optional
$context->setTokenSeparator('/') // depending of how your pubsub work, for example redis will be ':', websocket will be '/'
$router->setContext($context);
```

Match your first route !

```php
$channel = 'notification/user/admin/billing-app/639409'; // 'notification/user/admin/billing-app/*' work :)

list($routeName, $route, $attributes) = $router->match($channel);

// $router->match($channel, ':'); if you want override tokenSeparator from context, or if you dont have context.

// $routeName -> 'user_app_notification
// $route -> instance of Gos\Bundle\PubSubRouterBundle\Router\RouteInterface
// $attributes -> [ 'role' => 'admin', 'application' => 'billing-app', 'user_ref' => '639409' ]
```

What about miss match humm ?

```php
use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;

$channel = 'notification/user/admin/billing-app/azerty'; // will miss match

try {
    list($routeName, $route, $attributes) = $router->match($channel);
} catch (ResourceNotFoundException $e) {
    //handle exception
}
```

### Router CLI

`php app/console gos:prouter:debug` dump all registered routes

## License

MIT, See `LICENSE` file in the root of project.



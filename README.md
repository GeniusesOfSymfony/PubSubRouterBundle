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
* [x] Route generator

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
    routers:
        websocket: #available from container through gos_pubsub_router.websocket
            context:
                tokenSeparator: '/'
            resources:
                - @GosNotificationBundle/Resources/config/pubsub/websocket/notification.yml
        redis: #available from container through gos_pubsub_router.redis
            context:
                tokenSeparator: ':' #redis channel are like : notification:user:user2 so the token separator is :
            resources:
                - @GosNotificationBundle/Resources/config/pubsub/redis/notification.yml
```

**NOTE** : Each router are insulated. If you have several routers in the same class you will need to inject each router that you need.

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

**NOTE** : Wildcard match with `*`and `all` keyword

**NOTE 2** : Callback is not type hinted, so you can pass a single string (like a service name) that only depend of your implementation when you retrieve the route ! You are free to choose what you to do !

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

//$request->getAttributes->get('user_ref'); it's a parameterBag

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

- If you only need to generate route, type hint against `Gos\Bundle\PubSubRouterBundle\Generator\GeneratorInterface`
- If you only need to match route, type hint against `Gos\Bundle\PubSubRouterBundle\Matcher\MatcherInterface`
- If you need both, type hint against `Gos\Bundle\PubSubRouterBundle\Router\RouterInterface`

### Router CLI

`php app/console gos:prouter:debug -r websocket` dump all registered routes for websocket router

## License

MIT, See `LICENSE` file in the root of project.



list_routes
-----------

- Pattern: routes
- Pattern Regex: #^routes$#sD
- Callback: Gos\Bundle\PubSubRouterBundle\Tests\Console\Descriptor\DescriptorProvider, getRoutes
- Requirements: NO CUSTOM
- Class: Gos\Bundle\PubSubRouterBundle\Router\Route
- Defaults: NONE
- Options: 
    - `compiler_class`: Gos\Bundle\PubSubRouterBundle\Router\RouteCompiler


user_chat
---------

- Pattern: chat/{user}
- Pattern Regex: #^chat(?:/(?P<user>\d+))?$#sD
- Callback: strlen()
- Requirements: 
    - `user`: \d+
- Class: Gos\Bundle\PubSubRouterBundle\Router\Route
- Defaults: 
    - `user`: 42
- Options: 
    - `compiler_class`: Gos\Bundle\PubSubRouterBundle\Router\RouteCompiler
    - `foo`: bar


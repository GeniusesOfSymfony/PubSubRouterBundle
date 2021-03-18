<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

final class DescriptorProvider
{
    /**
     * @return array<string, RouteCollection>
     */
    public static function getRouteCollections(): array
    {
        $collection = new RouteCollection();

        foreach (self::getRoutes() as $name => $route) {
            $collection->add($name, $route);
        }

        return ['test_collection' => $collection];
    }

    /**
     * @return array<string, Route>
     */
    public static function getRoutes(): array
    {
        return [
            'list_routes' => new Route(
                'routes',
                [self::class, 'getRoutes']
            ),
            'user_chat' => new Route(
                'chat/{user}',
                'strlen',
                [
                    'user' => 42,
                ],
                [
                    'user' => '\d+',
                ],
                [
                    'foo' => 'bar',
                ]
            ),
        ];
    }
}

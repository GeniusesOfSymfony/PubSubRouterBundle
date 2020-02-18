<?php

use Gos\Bundle\PubSubRouterBundle\Loader\PhpFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

/** @var PhpFileLoader $loader */
$collection = new RouteCollection();
$collection->add(
    'user_chat',
    new Route(
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
    )
);

return $collection;

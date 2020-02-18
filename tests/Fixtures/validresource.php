<?php

use Gos\Bundle\PubSubRouterBundle\Loader\PhpFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

/** @var PhpFileLoader $loader */

/** @var RouteCollection $collection */
$collection = $loader->import('validchannel.php');
$collection->addDefaults(
    [
        'user' => 123,
    ]
);

$collection->addOptions(
    [
        'foo' => 'car',
    ]
);

return $collection;

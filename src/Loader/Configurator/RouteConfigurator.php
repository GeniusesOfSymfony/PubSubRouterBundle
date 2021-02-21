<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader\Configurator;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

final class RouteConfigurator
{
    use Traits\AddTrait;
    use Traits\RouteTrait;

    private ?CollectionConfigurator $parentConfigurator;

    public function __construct(RouteCollection $collection, RouteCollection | Route $route, string $name = '', CollectionConfigurator $parentConfigurator = null)
    {
        $this->collection = $collection;
        $this->route = $route;
        $this->name = $name;
        $this->parentConfigurator = $parentConfigurator; // for GC control
    }
}

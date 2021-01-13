<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader\Configurator\Traits;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

trait RouteTrait
{
    /**
     * @var RouteCollection|Route
     */
    private $route;

    /**
     * Adds defaults.
     *
     * @return $this
     */
    final public function defaults(array $defaults): self
    {
        $this->route->addDefaults($defaults);

        return $this;
    }

    /**
     * Adds requirements.
     *
     * @return $this
     */
    final public function requirements(array $requirements): self
    {
        $this->route->addRequirements($requirements);

        return $this;
    }

    /**
     * Adds options.
     *
     * @return $this
     */
    final public function options(array $options): self
    {
        $this->route->addOptions($options);

        return $this;
    }
}

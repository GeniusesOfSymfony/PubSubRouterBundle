<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

interface RouteInterface
{
    /**
     * @return string
     */
    public function getPattern();

    /**
     * @return Callable|string
     */
    public function getCallback();

    /**
     * @return array
     */
    public function getRequirements();

    /**
     * @return array
     */
    public function getArgs();

    /**
     * @param string $name
     */
    public function setName($name);
}

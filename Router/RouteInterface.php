<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

interface RouteInterface extends \Serializable
{
    /**
     * @return string
     */
    public function getPattern();

    /**
     * @return callable|string
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
}

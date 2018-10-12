<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator;

interface GeneratorInterface
{
    /**
     * @param string $routeName
     * @param array  $parameters
     *
     * @return string
     */
    public function generate($routeName, array $parameters = []);
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;

interface GeneratorInterface
{
    /**
     * @throws ResourceNotFoundException if the given route name does not exist
     */
    public function generate(string $routeName, array $parameters = []): string;
}

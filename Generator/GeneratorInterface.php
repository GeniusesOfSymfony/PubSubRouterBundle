<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator;

interface GeneratorInterface
{
    public function generate(string $routeName, array $parameters = []): string;
}

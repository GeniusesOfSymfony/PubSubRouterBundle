<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;

class CompiledGenerator extends Generator
{
    /**
     * @var array<string, array>
     */
    private $compiledRoutes;

    /**
     * @param array<string, array> $compiledRoutes
     */
    public function __construct(array $compiledRoutes)
    {
        $this->compiledRoutes = $compiledRoutes;
    }

    /**
     * @throws ResourceNotFoundException if the given route name does not exist
     */
    public function generate(string $routeName, array $parameters = []): string
    {
        if (!isset($this->compiledRoutes[$routeName])) {
            throw new ResourceNotFoundException(sprintf('Unable to generate a path for the named route "%s" as such route does not exist.', $routeName));
        }

        [$variables, $defaults, $requirements, $tokens] = $this->compiledRoutes[$routeName];

        return $this->doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $routeName);
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator;

use Gos\Bundle\PubSubRouterBundle\Exception\InvalidParameterException;
use Gos\Bundle\PubSubRouterBundle\Exception\MissingMandatoryParametersException;
use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

class Generator implements GeneratorInterface
{
    /**
     * @var RouteCollection
     */
    protected $routes;

    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @throws ResourceNotFoundException if the given route name does not exist
     */
    public function generate(string $routeName, array $parameters = []): string
    {
        if (null === $route = $this->routes->get($routeName)) {
            throw new ResourceNotFoundException(sprintf('Unable to generate a path for the named route "%s" as such route does not exist.', $routeName));
        }

        // the Route has a cache of its own and is not recompiled as long as it does not get modified
        $compiledRoute = $route->compile();

        return $this->doGenerate($compiledRoute->getVariables(), $route->getDefaults(), $route->getRequirements(), $compiledRoute->getTokens(), $parameters, $routeName);
    }

    /**
     * @throws MissingMandatoryParametersException when some parameters are missing that are mandatory for the route
     * @throws InvalidParameterException           when a parameter value for a placeholder is not correct because it does not match the requirement
     */
    protected function doGenerate(array $variables, array $defaults, array $requirements, array $tokens, array $parameters, string $routeName): string
    {
        $variables = array_flip($variables);
        $mergedParams = array_replace($defaults, $parameters);

        // all params must be given
        if ($diff = array_diff_key($variables, $mergedParams)) {
            throw new MissingMandatoryParametersException(sprintf('Some mandatory parameters are missing ("%s") to generate a path for route "%s".', implode('", "', array_keys($diff)), $routeName));
        }

        $url = '';
        $optional = true;
        $message = 'Parameter "{parameter}" for route "{route}" must match "{expected}" ("{given}" given) to generate a corresponding path.';

        foreach ($tokens as $token) {
            if ('variable' === $token[0]) {
                $varName = $token[3];
                // variable is not important by default
                $important = $token[5] ?? false;

                if (!$optional || $important || !\array_key_exists($varName, $defaults) || (null !== $mergedParams[$varName] && (string) $mergedParams[$varName] !== (string) $defaults[$varName])) {
                    // check requirement
                    if (!preg_match('#^'.preg_replace('/\(\?(?:=|<=|!|<!)((?:[^()\\\\]+|\\\\.|\((?1)\))*)\)/', '', $token[2]).'$#i'.(empty($token[4]) ? '' : 'u'), $mergedParams[$token[3]] ?? '')) {
                        throw new InvalidParameterException(strtr($message, ['{parameter}' => $varName, '{route}' => $routeName, '{expected}' => $token[2], '{given}' => $mergedParams[$varName]]));
                    }

                    $url = $token[1].$mergedParams[$varName].$url;
                    $optional = false;
                }
            } else {
                // static text
                $url = $token[1].$url;
                $optional = false;
            }
        }

        return $url;
    }
}

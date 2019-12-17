<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class Matcher implements MatcherInterface
{
    public const REQUIREMENT_MATCH = 0;
    public const REQUIREMENT_MISMATCH = 1;
    public const ROUTE_MATCH = 2;

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
    public function match(string $channel): array
    {
        if ($ret = $this->matchCollection($channel, $this->routes)) {
            return $ret;
        }

        throw new ResourceNotFoundException(sprintf('No routes found for "%s".', $channel));
    }

    /**
     * @return array|null containing the matched route name, the Route object, and the request attributes
     */
    protected function matchCollection(string $channel, RouteCollection $routes): ?array
    {
        /**
         * @var string $name
         * @var Route  $route
         */
        foreach ($routes as $name => $route) {
            $compiledRoute = $route->compile();

            // check the static prefix of the URL first. Only use the more expensive preg_match when it matches
            if ('' !== $compiledRoute->getStaticPrefix() && 0 !== strpos($channel, $compiledRoute->getStaticPrefix())) {
                continue;
            }

            if (!preg_match($compiledRoute->getRegex(), $channel, $matches)) {
                continue;
            }

            $status = [self::REQUIREMENT_MATCH, null];

            return [$name, $route, $this->getAttributes($route, $name, array_replace($matches, $status[1] ?? []))];
        }

        return null;
    }

    /**
     * Returns an array of values to use as request attributes.
     *
     * As this method requires the Route object, it is not available
     * in matchers that do not have access to the matched Route instance
     * (like the PHP and Apache matcher dumpers).
     *
     * @param Route  $route      The route we are matching against
     * @param string $name       The name of the route
     * @param array  $attributes An array of attributes from the matcher
     *
     * @return array An array of parameters
     */
    protected function getAttributes(Route $route, string $name, array $attributes): array
    {
        return $this->mergeDefaults($attributes, $route->getDefaults());
    }

    protected function mergeDefaults(array $params, array $defaults): array
    {
        foreach ($params as $key => $value) {
            if (!\is_int($key) && null !== $value) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }
}

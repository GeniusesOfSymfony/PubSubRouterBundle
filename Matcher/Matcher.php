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
    const REQUIREMENT_MATCH = 0;
    const REQUIREMENT_MISMATCH = 1;
    const ROUTE_MATCH = 2;

    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @param RouteCollection $routes
     */
    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function setCollection(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function match($channel)
    {
        if ($ret = $this->matchCollection($channel, $this->routes)) {
            return $ret;
        }

        throw new ResourceNotFoundException(sprintf('No routes found for "%s".', $channel));
    }

    /**
     * Tries to match a URL with a set of routes.
     *
     * @param string          $channel
     * @param RouteCollection $routes
     *
     * @return array
     */
    protected function matchCollection($channel, RouteCollection $routes)
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

            return [$name, $route, $this->getAttributes($route, $name, array_replace($matches, isset($status[1]) ? $status[1] : []))];
        }
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
    protected function getAttributes(Route $route, $name, array $attributes)
    {
        return $this->mergeDefaults($attributes, $route->getDefaults());
    }

    /**
     * Get merged default parameters.
     *
     * @param array $params
     * @param array $defaults
     *
     * @return array
     */
    protected function mergeDefaults($params, $defaults)
    {
        foreach ($params as $key => $value) {
            if (!\is_int($key) && null !== $value) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }
}

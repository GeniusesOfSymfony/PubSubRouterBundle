<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Router\Route;

class CompiledMatcher extends Matcher
{
    /**
     * @var array
     */
    private $staticRoutes;

    /**
     * @var array
     */
    private $regexpList;

    /**
     * @var array
     */
    private $dynamicRoutes;

    public function __construct(array $compiledRoutes)
    {
        [$this->staticRoutes, $this->regexpList, $this->dynamicRoutes] = $compiledRoutes;
    }

    public function match(string $channel): array
    {
        if ($ret = $this->doMatch($channel)) {
            return $ret;
        }

        throw new ResourceNotFoundException(sprintf('No routes found for "%s".', $channel));
    }

    private function doMatch(string $channel): array
    {
        foreach ($this->staticRoutes[$channel] ?? [] as [$name, $vars, $pattern, $callback, $defaults, $requirements, $options]) {
            $route = new Route($pattern, $callback, $defaults, $requirements, $options);
            $route->compile();

            return [$name, $route, $this->getAttributes($route, $channel, [self::REQUIREMENT_MATCH, []])];
        }

        foreach ($this->regexpList as $offset => $regex) {
            while (preg_match($regex, $channel, $matches)) {
                foreach ($this->dynamicRoutes[$m = (int) $matches['MARK']] as [$name, $vars, $pattern, $callback, $defaults, $requirements, $options]) {
                    $ret = [];

                    foreach ($vars as $i => $v) {
                        if (isset($matches[1 + $i])) {
                            $ret[$v] = $matches[1 + $i];
                        }
                    }

                    $route = new Route($pattern, $callback, $defaults, $requirements, $options);
                    $route->compile();

                    $status = [self::REQUIREMENT_MATCH, null];

                    return [$name, $route, $this->getAttributes($route, $channel, array_replace($ret, $status[1] ?? []))];
                }

                $regex = substr_replace($regex, 'F', $m - $offset, 1 + \strlen((string) $m));
                $offset += \strlen((string) $m);
            }
        }

        return [];
    }
}

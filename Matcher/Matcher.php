<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\InvalidArgumentException;
use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class Matcher implements MatcherInterface
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * {@inheritdoc}
     */
    public function match($channel, RouteCollection $routeCollection, $tokenSeparator)
    {
        /*
         * @var string
         * @var Route
         */
        foreach ($routeCollection as $routeName => $route) {
            if ($this->compare($route, $channel, $tokenSeparator)) {
                return [$routeName, $route, $this->attributes];
            }
        }

        throw new ResourceNotFoundException();
    }

    /**
     * {@inheritdoc}
     */
    public function compare(Route $route, $expected, $tokenSeparator)
    {
        $this->attributes = [];
        $expectedTokens =  $this->tokenize($expected, $tokenSeparator);
        $routeTokens = $this->tokenize($route, $tokenSeparator);

        if (($length = count($routeTokens)) !== count($expectedTokens)) {
            return false;
        }

        $startIndex = $length - 1;
        $requirements = $route->getRequirements();
        $hasRequirements = !empty($requirements);
        $validTokens = 0;

        for ($i = $startIndex;$i >= 0;$i--) {
            /** @var Token $routeToken */
            $routeToken = $routeTokens[$i];
            /** @var Token $expectedToken */
            $expectedToken = $expectedTokens[$i];

            if ($hasRequirements && $routeToken->isParameter()) {
                $this->attributes[$routeToken->getExpression()] = $expectedToken->getExpression();
                $tokenRequirements = $routeToken->getRequirements();

                if (empty($tokenRequirements)) {
                    if ($routeToken->getExpression() === $expectedToken->getExpression()) {
                        ++$validTokens;
                    }
                } else {
                    $checkPattern = true;

                    //Wildcard requirements
                    if (isset($tokenRequirements['wildcard']) && true === $tokenRequirements['wildcard']) {
                        if ($expectedToken->getExpression() === '*' || $expectedToken->getExpression() === 'all') {
                            ++$validTokens;
                            $checkPattern = false;
                        }
                    }

                    //Pattern requirements
                    if (true === $checkPattern && isset($tokenRequirements['pattern'])) {
                        $pattern = $tokenRequirements['pattern'];
                        if (1 === preg_match("#^$pattern#i", $expectedToken->getExpression())) {
                            ++$validTokens;
                        }
                    }
                }
            } else {
                if ($routeToken->getExpression() === $expectedToken->getExpression()) {
                    ++$validTokens;
                }
            }
        }

        return $validTokens === $length;
    }

    /**
     * @param string|Route $stringOrRoute
     * @param string       $separator
     *
     * @return Token[]
     *
     * @throws InvalidArgumentException
     */
    public function tokenize($stringOrRoute, $separator)
    {
        if ($stringOrRoute instanceof Route) {
            $pattern = $stringOrRoute->getPattern();
            $requirements = $stringOrRoute->getRequirements();
        } else {
            $pattern = $stringOrRoute;
        }

        $rawTokens = explode($separator, $pattern);
        $tokens = [];
        $requirementsSeen = [];
        $parametersSeen = [];

        foreach ($rawTokens as $i => $rawToken) {
            $token = new Token();
            $split = str_split($rawToken);
            reset($split);

            if (current($split) === '{' && end($split) === '}') {
                $token->setParameter();
                unset($split[0], $split[count($split)]);
            }

            $token->setExpression(implode($split));

            if ($token->isParameter()) {
                $parametersSeen[] = $token->getExpression();
            }

            if ($stringOrRoute instanceof Route) {
                if (!empty($stringOrRoute->getRequirements())) {
                    if (isset($requirements[$token->getExpression()])) {
                        $requirementsSeen[] = $token->getExpression();
                        $token->setRequirements($requirements[$token->getExpression()]);
                    }
                }
            }

            $tokens[$i] = $token;
        }

        if ($stringOrRoute instanceof Route) {
            if ($requirementsSeen !== $parametersSeen) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown parameter %s in [ %s ]',
                    implode(', ', array_diff($parametersSeen, $requirementsSeen)),
                    implode(', ', $parametersSeen)
                ));
            }
        }

        return $tokens;
    }
}

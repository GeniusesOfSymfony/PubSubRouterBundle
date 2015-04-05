<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Tokenizer\Token;
use Gos\Bundle\PubSubRouterBundle\Tokenizer\TokenizerInterface;

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
     * @var TokenizerInterface
     */
    protected $tokenizer;

    /**
     * @var RouteCollection
     */
    protected $collection;

    /**
     * @param TokenizerInterface $tokenizer
     */
    public function __construct(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;
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
    public function match($channel, $tokenSeparator)
    {
        /*
         * @var string
         * @var RouteInterface Route
         */
        foreach ($this->collection as $routeName => $route) {
            if ($this->compare($route, $channel, $tokenSeparator)) {
                $route->setName($routeName);

                return [$routeName, $route, $this->attributes];
            }
        }

        throw new ResourceNotFoundException();
    }

    /**
     * @param Route  $route
     * @param string $expected
     *
     * @return bool
     */
    protected function compare(Route $route, $expected, $tokenSeparator)
    {
        $this->attributes = [];
        $expectedTokens =  $this->tokenizer->tokenize($expected, $tokenSeparator);
        $routeTokens = $this->tokenizer->tokenize($route, $tokenSeparator);

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

        return $validTokens === $length && 0 !== $validTokens;
    }
}

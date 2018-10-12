<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator;

use Gos\Bundle\PubSubRouterBundle\Exception\InvalidArgumentException;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Tokenizer\Token;

interface GeneratorInterface
{
    /**
     * @param string          $routeName
     * @param array           $parameters
     * @param RouteCollection $routeCollection
     * @param null|string     $tokenSeparator
     *
     * @return mixed
     */
    public function generate($routeName, array $parameters = [], $tokenSeparator);

    /**
     * @param Token[]     $tokens
     * @param array       $parameters
     * @param string|null $tokenSeparator
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function generateFromTokens(Route $route, array $tokens, array $parameters = [], $tokenSeparator);

    /**
     * @param RouteCollection $collection
     */
    public function setCollection(RouteCollection $collection);
}

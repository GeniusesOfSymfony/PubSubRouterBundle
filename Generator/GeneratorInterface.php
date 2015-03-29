<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator;

use Gos\Bundle\PubSubRouterBundle\Exception\InvalidArgumentException;
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
    public function generate($routeName, Array $parameters = [], $tokenSeparator = null);

    /**
     * @param Token[] $tokens
     * @param array   $parameters
     * @param string  $tokenSeparator
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function generateFromTokens(Array $tokens, Array $parameters = [], $tokenSeparator);
}

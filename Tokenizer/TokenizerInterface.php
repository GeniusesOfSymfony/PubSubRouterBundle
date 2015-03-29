<?php

namespace Gos\Bundle\PubSubRouterBundle\Tokenizer;

use Gos\Bundle\PubSubRouterBundle\Exception\InvalidArgumentException;
use Gos\Bundle\PubSubRouterBundle\Router\Route;

interface TokenizerInterface
{
    /**
     * @param string|Route $stringOrRoute
     * @param string       $separator
     *
     * @return Token[]|false
     *
     * @throws InvalidArgumentException
     */
    public function tokenize($stringOrRoute, $separator);
}

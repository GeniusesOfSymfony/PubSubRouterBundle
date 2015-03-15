<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class RouterContext
{
    /**
     * @var string
     */
    protected $tokenSeparator;

    /**
     * @return string
     */
    public function getTokenSeparator()
    {
        return $this->tokenSeparator;
    }

    /**
     * @param string $tokenSeparator
     *
     * @return $this|RouterContext
     */
    public function setTokenSeparator($tokenSeparator)
    {
        $this->tokenSeparator = $tokenSeparator;

        return $this;
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

use Gos\Bundle\PubSubRouterBundle\Generator\GeneratorInterface;
use Gos\Bundle\PubSubRouterBundle\Matcher\MatcherInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
interface RouterInterface extends MatcherInterface, GeneratorInterface
{
    /**
     * @param RouterContext $context
     */
    public function setContext(RouterContext $context);

    /**
     * @return RouterContext
     */
    public function getContext();

    /**
     * @return RouteCollection
     */
    public function getCollection();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $routeName
     * @param array  $parameters
     * @param null   $tokenSeparator
     *
     * @return mixed
     */
    public function generate($routeName, Array $parameters = [], $tokenSeparator = null);

    /**
     * @param string $channel
     * @param null   $tokenSeparator
     *
     * @return mixed
     */
    public function match($channel, $tokenSeparator = null);
}

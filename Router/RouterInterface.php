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
     * @return RouteCollection
     */
    public function getCollection();

    /**
     * @return string
     */
    public function getName();
}

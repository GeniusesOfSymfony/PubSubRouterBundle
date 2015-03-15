<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
interface MatcherInterface
{
    public function match($channel, $tokenSeparator);
}

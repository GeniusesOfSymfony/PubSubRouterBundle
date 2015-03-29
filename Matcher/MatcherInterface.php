<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
interface MatcherInterface
{
    /**
     * @param string $channel
     * @param string $tokenSeparator
     *
     * @return bool
     *
     * @throws ResourceNotFoundException
     */
    public function match($channel, $tokenSeparator);
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher;

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
interface MatcherInterface
{
    /**
     * @throws ResourceNotFoundException
     */
    public function match(string $channel): array;
}

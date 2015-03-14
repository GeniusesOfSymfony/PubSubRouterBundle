<?php

namespace Gos\Bundle\PubSubRouterBundle;

use Gos\Bundle\PubSubRouterBundle\DependencyInjection\GosPubSubRouterExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class GosPubSubRouterBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new GosPubSubRouterExtension();
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Console\Helper;

use Gos\Bundle\PubSubRouterBundle\Console\Descriptor\TextDescriptor;
use Symfony\Component\Console\Helper\DescriptorHelper as BaseDescriptorHelper;

/**
 * @internal
 */
final class DescriptorHelper extends BaseDescriptorHelper
{
    public function __construct()
    {
        $this
            ->register('txt', new TextDescriptor())
        ;
    }
}

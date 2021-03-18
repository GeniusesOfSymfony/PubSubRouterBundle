<?php

namespace Gos\Bundle\PubSubRouterBundle\Console\Helper;

use Gos\Bundle\PubSubRouterBundle\Console\Descriptor\JsonDescriptor;
use Gos\Bundle\PubSubRouterBundle\Console\Descriptor\MarkdownDescriptor;
use Gos\Bundle\PubSubRouterBundle\Console\Descriptor\TextDescriptor;
use Gos\Bundle\PubSubRouterBundle\Console\Descriptor\XmlDescriptor;
use Symfony\Component\Console\Helper\DescriptorHelper as BaseDescriptorHelper;

/**
 * @internal
 */
final class DescriptorHelper extends BaseDescriptorHelper
{
    public function __construct()
    {
        $this
            ->register('json', new JsonDescriptor())
            ->register('md', new MarkdownDescriptor())
            ->register('txt', new TextDescriptor())
            ->register('xml', new XmlDescriptor())
        ;
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Console\Descriptor\XmlDescriptor;
use Symfony\Component\Console\Descriptor\DescriptorInterface;

final class XmlDescriptorTest extends AbstractDescriptorTestCase
{
    protected function getDescriptor(): DescriptorInterface
    {
        return new XmlDescriptor();
    }

    protected function getFormat(): string
    {
        return 'xml';
    }
}

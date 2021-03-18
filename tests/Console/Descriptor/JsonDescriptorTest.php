<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Console\Descriptor\JsonDescriptor;
use Symfony\Component\Console\Descriptor\DescriptorInterface;

final class JsonDescriptorTest extends AbstractDescriptorTestCase
{
    protected function getDescriptor(): DescriptorInterface
    {
        return new JsonDescriptor();
    }

    protected function getFormat(): string
    {
        return 'json';
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Console\Descriptor\MarkdownDescriptor;
use Symfony\Component\Console\Descriptor\DescriptorInterface;

final class MarkdownDescriptorTest extends AbstractDescriptorTestCase
{
    protected function getDescriptor(): DescriptorInterface
    {
        return new MarkdownDescriptor();
    }

    protected function getFormat(): string
    {
        return 'md';
    }
}

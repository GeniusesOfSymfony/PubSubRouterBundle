<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Console\Descriptor\TextDescriptor;
use Symfony\Component\Console\Descriptor\DescriptorInterface;

final class TextDescriptorTest extends AbstractDescriptorTestCase
{
    protected function setUp(): void
    {
        putenv('COLUMNS=121');
    }

    protected function tearDown(): void
    {
        putenv('COLUMNS');
    }

    protected function getDescriptor(): DescriptorInterface
    {
        return new TextDescriptor();
    }

    protected function getFormat(): string
    {
        return 'txt';
    }
}

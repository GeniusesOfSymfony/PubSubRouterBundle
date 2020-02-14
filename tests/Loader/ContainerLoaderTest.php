<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\ContainerLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class ContainerLoaderTest extends TestCase
{
    /**
     * @dataProvider supportsProvider
     */
    public function testSupports(bool $expected, string $type = null)
    {
        $this->assertSame($expected, (new ContainerLoader(new Container()))->supports('foo', $type));
    }

    public function supportsProvider()
    {
        return [
            [true, 'service'],
            [false, 'bar'],
            [false, null],
        ];
    }
}

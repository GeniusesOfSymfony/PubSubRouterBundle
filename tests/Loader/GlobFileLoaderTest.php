<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\GlobFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\GlobResource;

final class GlobFileLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $loader = new GlobFileLoader(new FileLocator());

        $this->assertTrue($loader->supports('any-path', 'glob'), '->supports() returns true if the resource has the glob type');
        $this->assertFalse($loader->supports('any-path'), '->supports() returns false if the resource is not of glob type');
    }

    public function testLoadAddsTheGlobResourceToTheContainer(): void
    {
        $loader = $this->createGlobFileLoaderWithoutImport(new FileLocator());
        $collection = $loader->load(__DIR__.'/../Fixtures/directory/*.yml');

        $this->assertEquals(new GlobResource(__DIR__.'/../Fixtures/directory', '/*.yml', false), $collection->getResources()[0]);
    }

    private function createGlobFileLoaderWithoutImport(FileLocator $locator): GlobFileLoader
    {
        // This is a rather flaky check, but it is one of the few things that exists in 4.x but not 5.x
        if (class_exists(FileLoaderLoadException::class)) {
            return new class($locator) extends GlobFileLoader {
                public function import($resource, $type = null, $ignoreErrors = false, $sourceResource = null)
                {
                    return new RouteCollection();
                }
            };
        } else {
            return new class($locator) extends GlobFileLoader {
                public function import($resource, string $type = null, bool $ignoreErrors = false, string $sourceResource = null, $exclude = null)
                {
                    return new RouteCollection();
                }
            };
        }
    }
}

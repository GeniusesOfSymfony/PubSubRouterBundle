<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Loader;

use Gos\Bundle\PubSubRouterBundle\Loader\ObjectLoader;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;

final class ObjectLoaderTest extends TestCase
{
    public function testLoadCallsServiceAndReturnsCollection(): void
    {
        $loader = new TestObjectLoader();

        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo', 'strlen'));

        $loader->loaderMap = [
            'my_route_provider_service' => $this->createLoaderService($collection),
        ];

        $actualRoutes = $loader->load(
            'my_route_provider_service::loadRoutes',
            'service'
        );

        $this->assertSame($collection, $actualRoutes);
        // the service file should be listed as a resource
        $this->assertNotEmpty($actualRoutes->getResources());
    }

    /**
     * @dataProvider getBadResourceStrings
     */
    public function testExceptionWithoutSyntax(string $resourceString): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $loader = new TestObjectLoader();
        $loader->load($resourceString);
    }

    public function getBadResourceStrings(): array
    {
        return [
            ['Foo:Bar:baz'],
            ['Foo::Bar::baz'],
            ['Foo:'],
            ['Foo::'],
            [':Foo'],
            ['::Foo'],
        ];
    }

    public function testExceptionOnNoObjectReturned(): void
    {
        $this->expectException(\TypeError::class);
        $loader = new TestObjectLoader();
        $loader->loaderMap = ['my_service' => 'NOT_AN_OBJECT'];
        $loader->load('my_service::method');
    }

    public function testExceptionOnBadMethod(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $loader = new TestObjectLoader();
        $loader->loaderMap = ['my_service' => new \stdClass()];
        $loader->load('my_service::method');
    }

    public function testExceptionOnMethodNotReturningCollection(): void
    {
        $service = new class() {
            public function loadRoutes(): string
            {
                return 'NOT_A_COLLECTION';
            }
        };

        $this->expectException(\LogicException::class);

        $loader = new TestObjectLoader();
        $loader->loaderMap = ['my_service' => $service];
        $loader->load('my_service::loadRoutes');
    }

    private function createLoaderService(RouteCollection $collection): object
    {
        return new class($collection) {
            private RouteCollection $collection;

            public function __construct(RouteCollection $collection)
            {
                $this->collection = $collection;
            }

            public function loadRoutes(): RouteCollection
            {
                return $this->collection;
            }
        };
    }
}

final class TestObjectLoader extends ObjectLoader
{
    /**
     * @var array<string, object>
     */
    public array $loaderMap = [];

    public function supports($resource, string $type = null): bool
    {
        return 'service' === $type;
    }

    protected function getObject(string $id): object
    {
        if (!isset($this->loaderMap[$id])) {
            throw new \InvalidArgumentException(sprintf('The "%s" ID is not registered.', $id));
        }

        return $this->loaderMap[$id];
    }
}

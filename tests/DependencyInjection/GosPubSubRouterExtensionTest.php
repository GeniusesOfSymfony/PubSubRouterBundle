<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\DependencyInjection;

use Gos\Bundle\PubSubRouterBundle\DependencyInjection\CompilerPass\RoutingResolverPass;
use Gos\Bundle\PubSubRouterBundle\DependencyInjection\GosPubSubRouterExtension;
use Gos\Bundle\PubSubRouterBundle\Router\RouterRegistry;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\ResourceCheckerConfigCacheFactory;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Reference;

final class GosPubSubRouterExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->setParameter('kernel.cache_dir', __DIR__);
        $this->container->setParameter('kernel.container_class', 'GosPubSubRouterBundleProjectContainer');
        $this->container->setParameter('kernel.debug', true);

        $this->registerService('config_cache_factory', ResourceCheckerConfigCacheFactory::class)
            ->addArgument(new TaggedIteratorArgument('config_cache.resource_checker'));
    }

    public function testContainerIsLoadedWithDefaultConfiguration(): void
    {
        $this->load();

        $this->assertContainerBuilderNotHasService('gos_pubsub_router.router.test');
    }

    public function dataSupportedExtensions(): \Generator
    {
        yield 'PHP file' => ['php'];
        yield 'XML file' => ['xml'];
        yield 'YAML file' => ['yml'];
    }

    /**
     * @dataProvider dataSupportedExtensions
     */
    public function testContainerIsLoadedWithASingleChannelFile(string $extension): void
    {
        $this->configureLoaderServices();

        $routerConfig = [
            'routers' => [
                'test' => [
                    'resources' => [
                        'validchannel.'.$extension,
                    ],
                ],
            ],
        ];

        $this->load($routerConfig);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'gos_pubsub_router.router.test',
            'setConfigCacheFactory',
            [new Reference('config_cache_factory')]
        );

        $registryDefinition = $this->container->getDefinition('gos_pubsub_router.router_registry');

        $this->assertCount(1, $registryDefinition->getMethodCalls(), 'The router should be added to the registry');

        // Make the registry public to use it
        $registryDefinition->setPublic(true);

        $this->compile();

        /** @var RouterRegistry $registry */
        $registry = $this->container->get('gos_pubsub_router.router_registry');
        $router = $registry->getRouter('test');

        $this->assertCount(1, $router->getCollection(), 'The routes are imported from the resource');
        $this->assertCount(1, $router->getCollection()->getResources(), 'The list of resources should contain the expected number of files');
    }

    /**
     * @dataProvider dataSupportedExtensions
     */
    public function testContainerIsLoadedWithAResourceFile(string $extension): void
    {
        $this->configureLoaderServices();

        $routerConfig = [
            'routers' => [
                'test' => [
                    'resources' => [
                        'validresource.'.$extension,
                    ],
                ],
            ],
        ];

        $this->load($routerConfig);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'gos_pubsub_router.router.test',
            'setConfigCacheFactory',
            [new Reference('config_cache_factory')]
        );

        $registryDefinition = $this->container->getDefinition('gos_pubsub_router.router_registry');

        $this->assertCount(1, $registryDefinition->getMethodCalls(), 'The router should be added to the registry');

        // Make the registry public to use it
        $registryDefinition->setPublic(true);

        $this->compile();

        /** @var RouterRegistry $registry */
        $registry = $this->container->get('gos_pubsub_router.router_registry');
        $router = $registry->getRouter('test');

        $this->assertCount(1, $router->getCollection(), 'The routes are imported from the resource');
        $this->assertCount(2, $router->getCollection()->getResources(), 'The list of resources should contain the expected number of files');
    }

    protected function getContainerExtensions(): array
    {
        return [
            new GosPubSubRouterExtension(),
        ];
    }

    private function configureLoaderServices(): void
    {
        $this->container->addCompilerPass(new RoutingResolverPass());

        $this->registerService('file_locator', FileLocator::class)
            ->addArgument(__DIR__.'/../Fixtures');

        $this->registerService('gos_pubsub_router.routing.resolver', LoaderResolver::class);
    }
}

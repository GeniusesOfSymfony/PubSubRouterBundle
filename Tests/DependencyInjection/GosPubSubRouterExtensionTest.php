<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\DependencyInjection;

use Gos\Bundle\PubSubRouterBundle\DependencyInjection\GosPubSubRouterExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class GosPubSubRouterExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->setParameter('kernel.cache_dir', __DIR__);
        $this->container->setParameter('kernel.container_class', 'GosPubSubRouterBundleProjectContainer');
        $this->container->setParameter('kernel.debug', true);
    }

    public function testContainerIsLoadedWithDefaultConfiguration()
    {
        $this->load();

        $this->assertContainerBuilderNotHasService('gos_pubsub_router.router.test');
        $this->assertContainerBuilderHasParameter('gos_pubsub_router.cache_class_prefix');
    }

    public function testContainerIsLoadedWithAConfiguredRouter()
    {
        $routerConfig = [
            'routers' => [
                'test' => [
                    'resources' => [
                        'routing.yml',
                    ],
                ],
            ],
        ];

        $this->load($routerConfig);

        $this->assertContainerBuilderHasService('gos_pubsub_router.router.test');

        $registryDefinition = $this->container->getDefinition('gos_pubsub_router.router.registry');

        $this->assertCount(1, $registryDefinition->getMethodCalls(), 'The router should be added to the registry');
    }

    protected function getContainerExtensions(): array
    {
        return [
            new GosPubSubRouterExtension(),
        ];
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\DependencyInjection\CompilerPass;

use Gos\Bundle\PubSubRouterBundle\DependencyInjection\CompilerPass\RoutingResolverPass;
use Gos\Bundle\PubSubRouterBundle\Loader\YamlFileLoader;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RoutingResolverPassTest extends AbstractCompilerPassTestCase
{
    public function testLoadersAreAddedToTheResolver(): void
    {
        $this->registerService('gos_pubsub_router.routing.resolver', LoaderResolver::class);
        $this->registerService('gos_pubsub_router.loader.yaml', YamlFileLoader::class)
            ->addTag('gos_pubsub_router.routing.loader');

        $this->compile();

        $this->assertContainerBuilderHasService('gos_pubsub_router.loader.yaml', YamlFileLoader::class);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'gos_pubsub_router.routing.resolver',
            'addLoader',
            [new Reference('gos_pubsub_router.loader.yaml')]
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RoutingResolverPass());
    }
}

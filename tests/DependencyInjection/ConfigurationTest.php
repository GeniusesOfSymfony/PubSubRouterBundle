<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\DependencyInjection;

use Gos\Bundle\PubSubRouterBundle\DependencyInjection\Configuration;
use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Router\Router;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);

        $this->assertEquals(self::getBundleDefaultConfig(), $config);
    }

    public function testWithARouterWithoutArrayResources(): void
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

        $normalizedRouterConfig = [
            'routers' => [
                'test' => [
                    'resources' => [
                        [
                            'resource' => 'routing.yml',
                            'type' => null,
                        ],
                    ],
                ],
            ],
        ];

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [$routerConfig]);

        $this->assertEquals(
            array_merge(self::getBundleDefaultConfig(), $normalizedRouterConfig),
            $config
        );
    }

    public function testWithARouterWithArrayResources(): void
    {
        $routerConfig = [
            'routers' => [
                'test' => [
                    'resources' => [
                        [
                            'resource' => 'routing.yml',
                            'type' => 'yaml',
                        ],
                    ],
                ],
            ],
        ];

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [$routerConfig]);

        $this->assertEquals(
            array_merge(self::getBundleDefaultConfig(), $routerConfig),
            $config
        );
    }

    public function testWithInvalidRouterNode(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The path "gos_pubsub_router.routers" should have at least 1 element(s) defined.');

        $processor = new Processor();
        $processor->processConfiguration(
            new Configuration(),
            [['routers' => []]]
        );
    }

    protected static function getBundleDefaultConfig(): array
    {
        return [
            'matcher_class' => Matcher::class,
            'generator_class' => Generator::class,
            'router_class' => Router::class,
            'routers' => [],
        ];
    }
}

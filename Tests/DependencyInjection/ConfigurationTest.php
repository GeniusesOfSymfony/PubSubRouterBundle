<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\DependencyInjection;

use Gos\Bundle\PubSubRouterBundle\DependencyInjection\Configuration;
use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Router\Router;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);

        $this->assertEquals(self::getBundleDefaultConfig(), $config);
    }

    public function testWithARouter()
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

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [$routerConfig]);

        $this->assertEquals(
            array_merge(self::getBundleDefaultConfig(), $routerConfig),
            $config
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The path "gos_pubsub_router.routers" should have at least 1 element(s) defined.
     */
    public function testWithInvalidRouterNode()
    {
        $processor = new Processor();
        $processor->processConfiguration(
            new Configuration(),
            [['routers' => []]]
        );
    }

    protected static function getBundleDefaultConfig()
    {
        return [
            'matcher_class' => Matcher::class,
            'generator_class' => Generator::class,
            'router_class' => Router::class,
            'routers' => [],
        ];
    }
}

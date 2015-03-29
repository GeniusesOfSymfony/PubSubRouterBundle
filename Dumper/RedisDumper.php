<?php

namespace Gos\Bundle\PubSubRouterBundle\Dumper;

use Gos\Bundle\PubSubRouterBundle\Generator\GeneratorInterface;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Tokenizer\TokenizerInterface;

class RedisDumper implements DumperInterface
{
    /**
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @var TokenizerInterface
     */
    protected $tokenizer;

    /**
     * @var RouteCollection
     */
    protected $routeCollection;

    const SUBSCRIBE = 'subscribe';

    const PSUBSCRIBE = 'psubsribe';

    /**
     * @param RouteCollection    $routeCollection
     * @param GeneratorInterface $generator
     * @param TokenizerInterface $tokenizer
     */
    public function __construct(RouteCollection $routeCollection, GeneratorInterface $generator, TokenizerInterface $tokenizer)
    {
        $this->router = $generator;
        $this->tokenizer = $tokenizer;
        $this->routeCollection = $routeCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function dump($tokenSeparator = ':', Array $extras = [])
    {;
        $subscriptions = [
            self::SUBSCRIBE => [],
            self::PSUBSCRIBE => [],
        ];

        /** @var Route $route */
        foreach ($this->routeCollection as $routeName => $route) {
            $tokens = $this->tokenizer->tokenize($route, $tokenSeparator);

            if (false === $tokens) {
                continue;
            }

            $attributeCount = 0;
            $wildcardAttribute = 0;
            $routeParameters = [];

            foreach ($tokens as $token) {
                if ($token->isParameter()) {
                    $attributeCount++;

                    $requirements = $token->getRequirements();

                    if (isset($requirements['wildcard']) && true === $requirements['wildcard']) {
                        $wildcardAttribute++;

                        $routeParameters[$token->getExpression()] = '*';
                    }
                }
            }

            if ($attributeCount === $wildcardAttribute && $wildcardAttribute > 0) {
                $subscriptions[self::PSUBSCRIBE][] = $this->router->generate(
                    $routeName,
                    $routeParameters,
                    $tokenSeparator
                );
            }
        }

        foreach ($extras as $extra) {
            list($routeName, $routeParameters) = $extra;

            $routeParameters = array_map(function ($value) {
                if ($value === 'all') {
                    return '*';
                }
            }, $routeParameters);

            $channel = $this->router->generate($routeName, $routeParameters, $tokenSeparator);

            if (false === strpos($channel, '*')) {
                $subscriptions[self::SUBSCRIBE][] = $channel;
            } else {
                $subscriptions[self::PSUBSCRIBE][] = $channel;
            }
        }

        return $subscriptions;
    }
}

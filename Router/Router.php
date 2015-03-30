<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

use Gos\Bundle\PubSubRouterBundle\Generator\GeneratorInterface;
use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoader;
use Gos\Bundle\PubSubRouterBundle\Matcher\MatcherInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class Router implements RouterInterface
{
    /**
     * @var RouteCollection
     */
    protected $collection;

    /**
     * @var RouterContext
     */
    protected $context;

    /**
     * @var MatcherInterface
     */
    protected $matcher;

    /**
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @var RouteLoader
     */
    protected $loader;

    /**
     * @param RouteCollection    $routeCollection
     * @param MatcherInterface   $matcher
     * @param GeneratorInterface $generator
     */
    public function __construct(
        RouteCollection $routeCollection,
        MatcherInterface $matcher,
        GeneratorInterface $generator,
        RouteLoader $loader
    ) {
        $this->collection = $routeCollection;
        $this->matcher = $matcher;
        $this->generator = $generator;
        $this->loader = $loader;

        //throw an event to dynamically add route from app before load ?
        $this->loader->load();
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RouterContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($routeName, Array $parameters = [], $tokenSeparator)
    {
        if (null === $tokenSeparator && null !== $this->context) {
            $tokenSeparator = $this->context->getTokenSeparator();
        }

        return $this->generator->generate($routeName, $parameters, $tokenSeparator);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFromTokens(Array $tokens, Array $parameters = [], $tokenSeparator)
    {
        if (null === $tokenSeparator && null !== $this->context) {
            $tokenSeparator = $this->context->getTokenSeparator();
        }

        return $this->generator->generateFromTokens($tokens, $parameters, $tokenSeparator);
    }

    /**
     * {@inheritdoc}
     */
    public function match($channel, $tokenSeparator = null)
    {
        if (null === $tokenSeparator && null !== $this->context) {
            $tokenSeparator = $this->context->getTokenSeparator();
        }

        return $this->matcher->match($channel, $tokenSeparator);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collection;
    }
}

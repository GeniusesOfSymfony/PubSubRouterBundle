<?php

namespace Gos\Bundle\PubSubRouterBundle\Cache;

use Doctrine\Common\Cache\Cache;
use Gos\Bundle\PubSubRouterBundle\Dumper\DumperInterface;
use Gos\Bundle\PubSubRouterBundle\Loader\RouteLoader;
use Gos\Bundle\PubSubRouterBundle\Tokenizer\TokenizerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class CacheWarmer implements CacheWarmerInterface
{
    /**
     * @var DumperInterface[]
     */
    protected $dumpers;

    /**
     * @var RouteLoader
     */
    protected $routeLoader;

    /**
     * @var TokenizerInterface
     */
    protected $tokenizer;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param Cache              $cache
     * @param RouteLoader        $routeLoader
     * @param TokenizerInterface $tokenizer
     */
    public function __construct(Cache $cache, RouteLoader $routeLoader, TokenizerInterface $tokenizer)
    {
        $this->cache = $cache;
        $this->tokenizer = $tokenizer;
        $this->routeLoader = $routeLoader;
        $this->dumpers = [];
    }

    /**
     * @param DumperInterface $dumper
     */
    public function addDumper(DumperInterface $dumper)
    {
        $this->dumpers[] = $dumper;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $routeCollection = $this->routeLoader->load();
        $this->cache->save('route_collection', $routeCollection);

        foreach ($this->dumpers as $dumper) {
            $this->cache->save($dumper->getName() . '_dumper', $dumper->dump());
        }

        foreach ($routeCollection as $name => $route) {
            //            $this->tokenizer->tokenize($route)
        }
    }
}

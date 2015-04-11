<?php

namespace Gos\Bundle\PubSubRouterBundle\Tokenizer;

use Doctrine\Common\Cache\Cache;
use Gos\Bundle\PubSubRouterBundle\Router\RouteInterface;

class TokenizerCacheDecorator implements TokenizerInterface
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var TokenizerInterface
     */
    protected $tokenizer;

    /**
     * @param TokenizerInterface $tokenizer
     * @param Cache              $cache
     */
    public function __construct(TokenizerInterface $tokenizer, Cache $cache)
    {
        $this->cache = $cache;
        $this->tokenizer = $tokenizer;
    }

    /**
     * {@inheritdoc}
     */
    public function tokenize($stringOrRoute, $separator)
    {
        if ($stringOrRoute instanceof RouteInterface) {
            $routeName = (string) $stringOrRoute;
            if ($tokens = $this->cache->fetch('tokens_' . $routeName)) {
                return $tokens;
            } else {
                $tokens = $this->tokenizer->tokenize($stringOrRoute, $separator);
                $this->cache->save('tokens_' . $routeName, $tokens);

                return $tokens;
            }
        }

        return $this->tokenizer->tokenize($stringOrRoute, $separator);
    }
}

<?php

namespace Gos\Bundle\PubSubRouterBundle\Cache;

use Doctrine\Common\Cache\Cache;

class PhpFileCacheDecorator implements Cache
{
    /** @var \Doctrine\Common\Cache\PhpFileCache */
    protected $cache;

    /** @var  bool */
    protected $debug;

    /**
     * @param string $cacheDir
     * @param bool   $debug
     */
    public function __construct($cacheDir, $debug)
    {
        $this->debug = $debug;
        $this->cache = new \Doctrine\Common\Cache\PhpFileCache($cacheDir, '.pubSubRouter.php');
        $this->cache->setNamespace('pubsub_router');
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        if (true === $this->debug) {
            return false;
        }

        return $this->cache->fetch($id);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        if (true === $this->debug) {
            return false;
        }

        return $this->cache->contains($id);
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        if (true === $this->debug) {
            return true;
        }

        return $this->cache->save($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->cache->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        return $this->cache->getStats();
    }
}

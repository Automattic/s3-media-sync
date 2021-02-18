<?php

namespace WPCOM_VIP\Aws;

use WPCOM_VIP\Doctrine\Common\Cache\Cache;
class DoctrineCacheAdapter implements \WPCOM_VIP\Aws\CacheInterface, \WPCOM_VIP\Doctrine\Common\Cache\Cache
{
    /** @var Cache */
    private $cache;
    public function __construct(\WPCOM_VIP\Doctrine\Common\Cache\Cache $cache)
    {
        $this->cache = $cache;
    }
    public function get($key)
    {
        return $this->cache->fetch($key);
    }
    public function fetch($key)
    {
        return $this->get($key);
    }
    public function set($key, $value, $ttl = 0)
    {
        return $this->cache->save($key, $value, $ttl);
    }
    public function save($key, $value, $ttl = 0)
    {
        return $this->set($key, $value, $ttl);
    }
    public function remove($key)
    {
        return $this->cache->delete($key);
    }
    public function delete($key)
    {
        return $this->remove($key);
    }
    public function contains($key)
    {
        return $this->cache->contains($key);
    }
    public function getStats()
    {
        return $this->cache->getStats();
    }
}

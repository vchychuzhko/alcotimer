<?php

namespace Awesome\Cache\Model;

class Cache
{
    private const CACHE_DIR = '/var/cache';

    /**
     * Retrieve cache by key.
     * @param string $key
     * @param string $tag
     * @return array
     */
    public function get($key = '', $tag = '')
    {
        $cache = $this->readCacheFile($key);

        if ($tag) {
            $data = $cache[$tag] ?? [];
        } else {
            $data = $cache;
        }

        return $data;
    }

    /**
     * Save data to cache.
     * @param string $key
     * @param string $tag
     * @param array $data
     * @return self
     */
    public function save($key, $tag, $data)
    {
        $cache = $this->get($key);
        $cache[$tag] = $data;

        $this->saveToCacheFile($key, $cache);

        return $this;
    }

    /**
     * Remove cache by key.
     * @param string $key
     * @param string $tag
     * @return self
     */
    public function remove($key = '', $tag = '')
    {
        if ($key) {
            $cache = $this->readCacheFile($key);
            unset($cache[$tag]);
            $this->saveToCacheFile($key, $cache);
        } else {
            @rmdir(BP . self::CACHE_DIR);
        }

        return $this;
    }

    /**
     * Read cache file.
     * @param string $key
     * @return array
     */
    public function readCacheFile($key)
    {
        $cache = @file_get_contents(BP . self::CACHE_DIR . '/' . $key . '-cache');

        return json_decode($cache, true) ?: [];
    }

    /**
     * Save data to cache file.
     * @param string $key
     * @param array $data
     * @return self
     */
    private function saveToCacheFile($key, $data)
    {
        if (!file_exists(BP . self::CACHE_DIR)) {
            mkdir(BP . self::CACHE_DIR);
        }

        file_put_contents(BP . self::CACHE_DIR . '/' . $key . '-cache', json_encode($data));

        return $this;
    }
}

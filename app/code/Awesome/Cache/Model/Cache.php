<?php

namespace Awesome\Cache\Model;

class Cache
{
    private const CACHE_DIR = '/var/cache';
    public const ETC_CACHE_KEY = 'etc';
    public const LAYOUT_CACHE_KEY = 'layout';
    public const FULL_PAGE_CACHE_KEY = 'full-page';

    /**
     * Retrieve cache by key.
     * @param string $key
     * @param string $tag
     * @return array
     */
    public function get($key, $tag = '')
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
     * @return $this
     */
    public function save($key, $tag, $data)
    {
        //@TODO: implement enable/disable cache functionality
        $cache = $this->get($key);
        $cache[$tag] = $data;

        $this->saveToCacheFile($key, $cache);

        return $this;
    }

    /**
     * Remove cache by key and tag.
     * If key is not specified, remove all caches.
     * @param string $key
     * @param string $tag
     * @return $this
     */
    public function remove($key = '', $tag = '')
    {
        if ($key && $tag) {
            $cache = $this->readCacheFile($key);
            unset($cache[$tag]);
            $this->saveToCacheFile($key, $cache);
        } else {
            $this->removeCacheFile($key);
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
     * @return $this
     */
    private function saveToCacheFile($key, $data)
    {
        if (!file_exists(BP . self::CACHE_DIR)) {
            mkdir(BP . self::CACHE_DIR);
        }

        file_put_contents(BP . self::CACHE_DIR . '/' . $key . '-cache', json_encode($data));

        return $this;
    }

    /**
     * Remove cache file according to key.
     * If key is not specified, remove all caches.
     * @param string $key
     * @return $this
     */
    private function removeCacheFile($key = '')
    {
        if ($key) {
            @unlink(BP . self::CACHE_DIR . '/' . $key . '-cache');
        } else {
            @rrmdir(BP . self::CACHE_DIR);
        }

        return $this;
    }
}

<?php

namespace Awesome\Cache\Model;

class Cache
{
    private const CACHE_DIR = BP . '/var/cache';
    private const ETC_CACHE_FILE = 'etc-cache';

    /**
     * Retrieve cache by key.
     * @param string $key
     * @return array
     */
    public function get($key = '')
    {
        $cache = $this->readCacheFile();

        if ($key) {
            $data = $cache[$key] ?? [];
        } else {
            $data = $cache;
        }

        return $data;
    }

    /**
     * Save data to cache.
     * @param array $data
     * @param string $key
     * @return self
     */
    public function save($data, $key)
    {
        $cache = $this->get();
        $cache[$key] = $data;

        $this->saveToCacheFile($cache);

        return $this;
    }

    /**
     * Remove cache by key.
     * @param string $key
     * @return self
     */
    public function remove($key = '')
    {
        if ($key) {
            $cache = $this->readCacheFile();
            unset($cache[$key]);
            $this->saveToCacheFile($cache);
        } else {
            @unlink(self::CACHE_DIR . '/' . self::ETC_CACHE_FILE);
        }

        return $this;
    }

    /**
     * Read cache file.
     * @return array
     */
    public function readCacheFile()
    {
        $cache = @file_get_contents(self::CACHE_DIR . '/' . self::ETC_CACHE_FILE);

        return json_decode($cache, true) ?: [];
    }

    /**
     * Save data to cache file.
     * @param array $data
     * @return self
     */
    private function saveToCacheFile($data)
    {
        if (!file_exists(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR);
        }

        file_put_contents(self::CACHE_DIR . '/' . self::ETC_CACHE_FILE, json_encode($data));

        return $this;
    }
}

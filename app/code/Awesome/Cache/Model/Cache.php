<?php

namespace Awesome\Cache\Model;

use Awesome\Framework\Model\FileManager;

class Cache implements \Awesome\Framework\Model\SingletonInterface
{
    private const CACHE_DIR = '/var/cache';
    public const ETC_CACHE_KEY = 'etc';
    public const LAYOUT_CACHE_KEY = 'layout';
    public const FULL_PAGE_CACHE_KEY = 'fullpage';

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * Cache constructor.
     * @param FileManager $fileManager
     */
    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * Retrieve cache by key and tag.
     * If tag is not provided, all data related to the key will be returned.
     * @param string $key
     * @param string $tag
     * @return mixed
     */
    public function get($key, $tag = '')
    {
        //@TODO: Possibly, rework it according to Symfony, with save callback
        $cache = $this->readCacheFile($key);

        if ($tag === '') {
            $data = $cache;
        } else {
            $data = $cache[$tag] ?? null;
        }

        return $data;
    }

    /**
     * Save data to cache.
     * @param string $key
     * @param string $tag
     * @param mixed $data
     * @return $this
     */
    public function save($key, $tag, $data)
    {
        //@TODO: implement enable/disable cache functionality
        $cache = $this->get($key);
        $cache[$tag] = $data;

        $this->saveCacheFile($key, $cache);

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
            $this->saveCacheFile($key, $cache);
        } else {
            $this->removeCache($key);
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
        $cache = $this->fileManager->readFile(BP . self::CACHE_DIR . '/' . $key . '-cache');

        return json_decode($cache, true) ?: [];
    }

    /**
     * Save data to cache file.
     * @param string $key
     * @param array $data
     * @return $this
     */
    private function saveCacheFile($key, $data)
    {
        if (!file_exists(BP . self::CACHE_DIR)) {
            $this->fileManager->createDirectory(BP . self::CACHE_DIR);
        }

        $this->fileManager->createFile(BP . self::CACHE_DIR . '/' . $key . '-cache', json_encode($data), true);

        return $this;
    }

    /**
     * Remove cache file according to key.
     * If key is not specified, remove all caches.
     * @param string $key
     * @return $this
     */
    private function removeCache($key = '')
    {
        if ($key) {
            $this->fileManager->removeFile(BP . self::CACHE_DIR . '/' . $key . '-cache');
        } else {
            $this->fileManager->removeDirectory(BP . self::CACHE_DIR);
        }

        return $this;
    }
}

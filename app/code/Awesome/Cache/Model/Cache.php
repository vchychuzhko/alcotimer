<?php
declare(strict_types=1);

namespace Awesome\Cache\Model;

use Awesome\Cache\Model\CacheState;
use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Serializer\Json;

class Cache implements \Awesome\Framework\Model\SingletonInterface
{
    private const CACHE_DIR = '/var/cache';

    public const ETC_CACHE_KEY = 'etc';
    public const LAYOUT_CACHE_KEY = 'layout';
    public const FULL_PAGE_CACHE_KEY = 'full_page';

    /**
     * @var CacheState $cacheState
     */
    private $cacheState;

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var Json $json
     */
    private $json;

    /**
     * Cache constructor.
     * @param CacheState $cacheState
     * @param FileManager $fileManager
     * @param Json $json
     */
    public function __construct(CacheState $cacheState, FileManager $fileManager, Json $json)
    {
        $this->cacheState = $cacheState;
        $this->fileManager = $fileManager;
        $this->json = $json;
    }

    /**
     * Retrieve cache by key and tag.
     * Callback function can be provided to save result value to cache immediately.
     * @param string $key
     * @param string $tag
     * @param callable|null $callback
     * @return mixed
     */
    public function get(string $key, string $tag, ?callable $callback = null)
    {
        $data = null;

        if ($this->cacheState->isEnabled($key)) {
            $cache = $this->readCacheFile($key);

            $data = $cache[$tag] ?? null;
        }

        if ($data === null && is_callable($callback)) {
            $data = $callback();
            $this->save($key, $tag, $data);
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
    public function save(string $key, string $tag, $data): self
    {
        if ($this->cacheState->isEnabled($key)) {
            $cache = $this->readCacheFile($key);
            $cache[$tag] = $data;

            $this->saveCacheFile($key, $cache);
        }

        return $this;
    }

    /**
     * Flush cache by key.
     * If key is not specified, invalidate all caches.
     * @param string $key
     * @return $this
     */
    public function invalidate(string $key = ''): self
    {
        if ($key === '') {
            $this->fileManager->removeDirectory(BP . self::CACHE_DIR);
        } else {
            $this->fileManager->removeFile(BP . self::CACHE_DIR . '/' . $key . '-cache');
        }

        return $this;
    }

    /**
     * Read cache file.
     * @param string $key
     * @return array
     */
    private function readCacheFile(string $key): array
    {
        $cache = $this->fileManager->readFile(BP . self::CACHE_DIR . '/' . $key . '-cache', true) ?: '{}';

        return $this->json->decode($cache);
    }

    /**
     * Save data to cache file.
     * @param string $key
     * @param array $data
     * @return $this
     */
    private function saveCacheFile(string $key, array $data): self
    {
        $this->fileManager->createFile(BP . self::CACHE_DIR . '/' . $key . '-cache', $this->json->encode($data), true);

        return $this;
    }
}

<?php
declare(strict_types=1);

namespace Awesome\Cache\Model;

use Awesome\Cache\Model\CacheState;
use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Serializer\Json;

class Cache
{
    private const CACHE_DIR = '/var/cache';
    private const CACHE_SUFFIX = '-cache';

    public const ETC_CACHE_KEY = 'etc';
    public const LAYOUT_CACHE_KEY = 'layout';
    public const FULL_PAGE_CACHE_KEY = 'full_page';
    public const TRANSLATIONS_CACHE_KEY = 'translations';

    private CacheState $cacheState;

    private FileManager $fileManager;

    private Json $json;

    /**
     * Cache constructor.
     * @param CacheState $cacheState
     * @param FileManager $fileManager
     * @param Json $json
     */
    public function __construct(
        CacheState $cacheState,
        FileManager $fileManager,
        Json $json
    ) {
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
            $cache = $this->readFromCacheFile($key);

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
            $cache = $this->readFromCacheFile($key);
            $cache[$tag] = $data;

            $this->saveToCacheFile($key, $cache);
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
            $this->fileManager->removeFile(BP . self::CACHE_DIR . '/' . $key . self::CACHE_SUFFIX);
        }

        return $this;
    }

    /**
     * Read cache file.
     * @param string $key
     * @return array
     */
    private function readFromCacheFile(string $key): array
    {
        $cache = $this->fileManager->readFile(BP . self::CACHE_DIR . '/' . $key . self::CACHE_SUFFIX, true) ?: '{}';

        return $this->json->decode($cache);
    }

    /**
     * Save data to cache file.
     * @param string $key
     * @param array $data
     */
    private function saveToCacheFile(string $key, array $data)
    {
        $this->fileManager->createFile(
            BP . self::CACHE_DIR . '/' . $key . self::CACHE_SUFFIX,
            $this->json->encode($data),
            true
        );
    }

    /**
     * Get all defined cache types.
     * @return array
     */
    public static function getAllTypes(): array
    {
        return [
            self::ETC_CACHE_KEY,
            self::LAYOUT_CACHE_KEY,
            self::FULL_PAGE_CACHE_KEY,
            self::TRANSLATIONS_CACHE_KEY,
        ];
    }
}

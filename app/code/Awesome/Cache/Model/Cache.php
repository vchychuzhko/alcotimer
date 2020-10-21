<?php
declare(strict_types=1);

namespace Awesome\Cache\Model;

use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Serializer\Json;

class Cache implements \Awesome\Framework\Model\SingletonInterface
{
    private const CACHE_DIR = '/var/cache';

    public const CACHE_CONFIG_PATH = 'cache';

    public const ETC_CACHE_KEY = 'etc';
    public const LAYOUT_CACHE_KEY = 'layout';
    public const FULL_PAGE_CACHE_KEY = 'full_page';

    /**
     * @var Config $config
     */
    private $config;

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
     * @param Config $config
     * @param FileManager $fileManager
     * @param Json $json
     */
    public function __construct(Config $config, FileManager $fileManager, Json $json)
    {
        $this->config = $config;
        $this->fileManager = $fileManager;
        $this->json = $json;
    }

    /**
     * Retrieve cache by key and tag.
     * @param string $key
     * @param string $tag
     * @return mixed
     */
    public function get(string $key, string $tag)
    {
        $data = null;

        if ($this->cacheTypeEnabled($key)) {
            $cache = $this->readCacheFile($key);

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
    public function save(string $key, string $tag, $data): self
    {
        if ($this->cacheTypeEnabled($key)) {
            $cache = $this->readCacheFile($key);
            $cache[$tag] = $data;

            $this->saveCacheFile($key, $cache);
        }

        return $this;
    }

    /**
     * Remove cache by key and tag.
     * If key is not specified, remove all caches.
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
     * Check if requested cache type is enabled.
     * @param string $key
     * @return bool
     */
    private function cacheTypeEnabled(string $key): bool
    {
        return (bool) $this->config->get(self::CACHE_CONFIG_PATH . '/' . $key);
    }

    /**
     * Get available cache types.
     * @return array
     */
    public function getTypes(): array
    {
        return [
            self::ETC_CACHE_KEY,
            self::LAYOUT_CACHE_KEY,
            self::FULL_PAGE_CACHE_KEY,
        ];
    }

    /**
     * Read cache file.
     * @param string $key
     * @return array
     */
    private function readCacheFile(string $key): array
    {
        $cache = $this->fileManager->readFile(BP . self::CACHE_DIR . '/' . $key . '-cache') ?: '{}';

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

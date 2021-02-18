<?php
declare(strict_types=1);

namespace Awesome\Cache\Model;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Config;

class CacheState
{
    private const CACHE_CONFIG_PATH = 'cache';

    /**
     * @var Config $config
     */
    private $config;

    /**
     * CacheState constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Check if requested cache type is enabled.
     * @param string $type
     * @return bool
     */
    public function isEnabled(string $type): bool
    {
        return (bool) $this->config->get(self::CACHE_CONFIG_PATH . '/' . $type);
    }

    /**
     * Enable requested cache type if exists.
     * @param string $type
     * @return bool
     */
    public function enable(string $type): bool
    {
        return $this->config->set(self::CACHE_CONFIG_PATH . '/' . $type, 1);
    }

    /**
     * Disable requested cache type if exists.
     * @param string $type
     * @return bool
     */
    public function disable(string $type): bool
    {
        return $this->config->set(self::CACHE_CONFIG_PATH . '/' . $type, 0);
    }

    /**
     * Get defined cache types.
     * @return array
     */
    public function getDefinedTypes(): array
    {
        return [
            Cache::ETC_CACHE_KEY,
            Cache::LAYOUT_CACHE_KEY,
            Cache::FULL_PAGE_CACHE_KEY,
            Cache::TRANSLATIONS_CACHE_KEY,
        ];
    }
}

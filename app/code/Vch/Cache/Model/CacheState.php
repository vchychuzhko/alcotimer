<?php
declare(strict_types=1);

namespace Vch\Cache\Model;

use Vch\Cache\Model\Cache;
use Vch\Framework\Model\Config;

class CacheState
{
    private const CACHE_CONFIG = 'cache';

    private Config $config;

    /**
     * CacheState constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Check if requested cache type is enabled.
     * @param string $type
     * @return bool
     */
    public function isEnabled(string $type): bool
    {
        return (bool) $this->config->get(self::CACHE_CONFIG . '/' . $type);
    }

    /**
     * Enable requested cache type if exists.
     * @param string $type
     * @return bool
     */
    public function enable(string $type): bool
    {
        return $this->config->set(self::CACHE_CONFIG . '/' . $type, 1);
    }

    /**
     * Disable requested cache type if exists.
     * @param string $type
     * @return bool
     */
    public function disable(string $type): bool
    {
        return $this->config->set(self::CACHE_CONFIG . '/' . $type, 0);
    }
}

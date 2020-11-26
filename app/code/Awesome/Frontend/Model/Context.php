<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Config;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\StaticContent;

class Context implements \Awesome\Framework\Model\SingletonInterface
{
    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var FrontendState $frontendState
     */
    private $frontendState;

    /**
     * @var StaticContent $staticContent
     */
    private $staticContent;

    /**
     * Template Context constructor.
     * @param Cache $cache
     * @param Config $config
     * @param FrontendState $frontendState
     * @param StaticContent $staticContent
     */
    public function __construct(
        Cache $cache,
        Config $config,
        FrontendState $frontendState,
        StaticContent $staticContent
    ) {
        $this->cache = $cache;
        $this->config = $config;
        $this->frontendState = $frontendState;
        $this->staticContent = $staticContent;
    }

    /**
     * Get cache object.
     * @return Cache
     */
    public function getCache(): Cache
    {
        return $this->cache;
    }

    /**
     * Get config object.
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Get frontend state object.
     * @return FrontendState
     */
    public function getFrontendState(): FrontendState
    {
        return $this->frontendState;
    }

    /**
     * Get static content object.
     * @return StaticContent
     */
    public function getStaticContent(): StaticContent
    {
        return $this->staticContent;
    }
}

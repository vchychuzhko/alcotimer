<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Config;

class AppState implements \Awesome\Framework\Model\SingletonInterface
{
    public const DEVELOPER_MODE = 'development';
    public const PRODUCTION_MODE = 'production';

    public const DEVELOPER_MODE_CONFIG = 'developer_mode';
    public const SHOW_FORBIDDEN_CONFIG = 'show_forbidden';
    public const WEB_ROOT_CONFIG = 'web/web_root_is_pub';

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var bool $isDeveloperMode
     */
    private $isDeveloperMode;

    /**
     * @var string $isPubRoot
     */
    private $isPubRoot;

    /**
     * @var bool $showForbidden
     */
    private $showForbidden;

    /**
     * AppState constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get application mode.
     * @return string
     */
    public function getAppMode(): string
    {
        return $this->isDeveloperMode() ? self::DEVELOPER_MODE : self::PRODUCTION_MODE;
    }

    /**
     * Check if application is in developer mode.
     * @return bool
     */
    public function isDeveloperMode(): bool
    {
        if ($this->isDeveloperMode === null) {
            $this->isDeveloperMode = (bool) $this->config->get(self::DEVELOPER_MODE_CONFIG);
        }

        return $this->isDeveloperMode;
    }

    /**
     * Check if application web root is configured to pub folder.
     * @return bool
     */
    public function isPubRoot(): bool
    {
        if ($this->isPubRoot === null) {
            $this->isPubRoot = (bool) $this->config->get(self::WEB_ROOT_CONFIG);
        }

        return $this->isPubRoot;
    }

    /**
     * Check if it is allowed to show 403 Forbidden response.
     * @return bool
     */
    public function showForbidden(): bool
    {
        if ($this->showForbidden === null) {
            $this->showForbidden = (bool) $this->config->get(self::SHOW_FORBIDDEN_CONFIG);
        }

        return $this->showForbidden;
    }
}

<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Config;

class AppState implements \Awesome\Framework\Model\SingletonInterface
{
    private const BACKEND_ENABLED_CONFIG = 'backend/enabled';
    private const DEVELOPER_MODE_CONFIG = 'developer_mode';
    private const REQUEST_LOG_CONFIG = 'request_log';

    protected Config $config;

    private bool $isAdminhtmlEnabled;

    private bool $isDeveloperMode;

    /**
     * AppState constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Check if adminhtml view is enabled.
     * @return bool
     */
    public function isAdminhtmlEnabled(): bool
    {
        if (!isset($this->isAdminhtmlEnabled)) {
            $this->isAdminhtmlEnabled = (bool) $this->config->get(self::BACKEND_ENABLED_CONFIG);
        }

        return $this->isAdminhtmlEnabled;
    }

    /**
     * Check if application is in developer mode.
     * @return bool
     */
    public function isDeveloperMode(): bool
    {
        if (!isset($this->isDeveloperMode)) {
            $this->isDeveloperMode = (bool) $this->config->get(self::DEVELOPER_MODE_CONFIG);
        }

        return $this->isDeveloperMode;
    }

    /**
     * Check if request logging is enabled.
     * @return bool
     */
    public function isRequestLogEnabled(): bool
    {
        if (!isset($this->isRequestLogEnabled)) {
            $this->isRequestLogEnabled = (bool) $this->config->get(self::REQUEST_LOG_CONFIG);
        }

        return $this->isRequestLogEnabled;
    }
}

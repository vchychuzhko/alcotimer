<?php
declare(strict_types=1);

namespace Awesome\Timer\Block;

use Awesome\Framework\Helper\DataHelper;

class Timer extends \Awesome\Frontend\Block\Template
{
    public const TIMER_CONFIG_PATH = 'timer_config';

    /**
     * Get random time range slider json.
     * @return string
     */
    public function getRandomRangeConfigJson(): string
    {
        $randomRangeConfig = $this->config->get(self::TIMER_CONFIG_PATH . '/random_range') ?: [];

        return $this->processConfig($randomRangeConfig);
    }

    /**
     * Get timer settings json.
     * @return string
     */
    public function getSettingsJson(): string
    {
        $settings = $this->config->get(self::TIMER_CONFIG_PATH . '/settings') ?: [];

        return $this->processConfig($settings);
    }

    /**
     * Get timer configurations.
     * @return string
     */
    public function getTimerConfigJson(): string
    {
        $timerConfig = $this->config->get(self::TIMER_CONFIG_PATH . '/timer') ?: [];

        if (isset($timerConfig['sound'])) {
            $timerConfig['sound'] = $this->getMediaFileUrl($timerConfig['sound']);
        }

        return $this->processConfig($timerConfig);
    }

    /**
     * Change config keys to camelCase and return config json.
     * @param array $config
     * @return string
     */
    private function processConfig(array $config): string
    {
        //@TODO: Rework Timer configurations structure and processing
        foreach ($config as $configKey => $configValue) {
            unset($config[$configKey]);
            $config[DataHelper::camelCase($configKey)] = $configValue;
        }

        return json_encode($config);
    }
}

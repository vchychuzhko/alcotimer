<?php

namespace Awesome\Timer\Block;

class Timer extends \Awesome\Base\Block\Template
{
    private const TIMER_CONFIG = 'timer_config';

    /**
     * Get random time range slider json.
     * @return string
     */
    public function getRandomRangeConfigJson()
    {
        $randomRangeConfig = $this->config->getConfig(self::TIMER_CONFIG . '/random_range') ?: [];

        return $this->processConfig($randomRangeConfig);
    }

    /**
     * Get timer settings json.
     * @return string
     */
    public function getSettings()
    {
        $settings = $this->config->getConfig(self::TIMER_CONFIG . '/settings') ?: [];

        return $this->processConfig($settings);
    }

    /**
     * Get timer time set by default.
     * @return string
     */
    public function getDefaultTime()
    {
        $timerConfig = $this->config->getConfig(self::TIMER_CONFIG . '/timer') ?? [];

        return $this->processConfig($timerConfig);
    }

    /**
     * Change config keys to camelCase and return config json.
     * @param array $config
     * @return string
     */
    private  function processConfig($config)
    {
        foreach ($config as $configKey => $configValue) {
            unset($config[$configKey]);
            $config[$this->camelCase($configKey)] = $configValue;
        }

        return json_encode($config);
    }
}

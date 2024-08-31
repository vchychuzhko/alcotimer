<?php
declare(strict_types=1);

namespace Vch\Timer\Block;

use Vch\Framework\Model\Config;
use Vch\Framework\Model\Serializer\Json;
use Vch\Frontend\Model\DeployedVersion;
use Vch\Frontend\Model\Layout;

class Timer extends \Vch\Frontend\Block\Template
{
    private const TIMER_CONFIG = 'timer';

    private Config $config;

    private Json $json;

    /**
     * Timer constructor.
     * @param Config $config
     * @param DeployedVersion $deployedVersion
     * @param Json $json
     * @param Layout $layout
     * @param string $nameInLayout
     * @param string|null $template
     * @param array $data
     */
    public function __construct(
        Config $config,
        DeployedVersion $deployedVersion,
        Json $json,
        Layout $layout,
        string $nameInLayout,
        ?string $template = null,
        array $data = []
    ) {
        parent::__construct($deployedVersion, $layout, $nameInLayout, $template, $data);
        $this->config = $config;
        $this->json = $json;
    }

    /**
     * Get random time slider json.
     * @return string
     */
    public function getSliderConfigJson(): string
    {
        $randomRangeConfig = $this->config->get(self::TIMER_CONFIG . '/slider_config') ?: [];

        return $this->json->encode($randomRangeConfig);
    }

    /**
     * Get timer settings json.
     * @return string
     */
    public function getSettingsJson(): string
    {
        $settings = $this->config->get(self::TIMER_CONFIG . '/settings') ?: [];

        return $this->json->encode($settings);
    }

    /**
     * Get timer configurations.
     * @return string
     */
    public function getTimerConfigJson(): string
    {
        $timerConfig = $this->config->get(self::TIMER_CONFIG . '/general') ?: [];

        if (isset($timerConfig['sound'])) {
            $timerConfig['sound'] = $this->getMediaFileUrl($timerConfig['sound']);
        }

        return $this->json->encode($timerConfig);
    }
}

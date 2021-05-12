<?php
declare(strict_types=1);

namespace Awesome\Timer\Block;

use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Serializer\Json;
use Awesome\Frontend\Model\Context;

class Timer extends \Awesome\Frontend\Block\Template
{
    public const TIMER_CONFIG_PATH = 'timer';

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var Json $json
     */
    private $json;

    /**
     * Timer constructor.
     * @param Config $config
     * @param Context $context
     * @param Json $json
     * @param array $data
     */
    public function __construct(Config $config, Context $context, Json $json, array $data = [])
    {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->json = $json;
    }

    /**
     * Get random time slider json.
     * @return string
     */
    public function getSliderConfigJson(): string
    {
        $randomRangeConfig = $this->config->get(self::TIMER_CONFIG_PATH . '/slider_config') ?: [];

        return $this->json->encode($randomRangeConfig);
    }

    /**
     * Get timer settings json.
     * @return string
     */
    public function getSettingsJson(): string
    {
        $settings = $this->config->get(self::TIMER_CONFIG_PATH . '/settings') ?: [];

        return $this->json->encode($settings);
    }

    /**
     * Get timer configurations.
     * @return string
     */
    public function getTimerConfigJson(): string
    {
        $timerConfig = $this->config->get(self::TIMER_CONFIG_PATH . '/general') ?: [];

        if (isset($timerConfig['sound'])) {
            $timerConfig['sound'] = $this->getMediaFileUrl($timerConfig['sound']);
        }

        return $this->json->encode($timerConfig);
    }
}

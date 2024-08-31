<?php
declare(strict_types=1);

namespace Vch\Frontend\Block;

use Vch\Framework\Model\Config;
use Vch\Frontend\Model\DeployedVersion;
use Vch\Frontend\Model\Layout;

class Menu extends \Vch\Frontend\Block\Template
{
    private const SUPPORT_EMAIL_CONFIG = 'support_email_address';
    //@TODO: move this to future Contact module
    private Config $config;

    /**
     * Menu constructor.
     * @param Config $config
     * @param DeployedVersion $deployedVersion
     * @param Layout $layout
     * @param string $nameInLayout
     * @param string|null $template
     * @param array $data
     */
    public function __construct(
        Config $config,
        DeployedVersion $deployedVersion,
        Layout $layout,
        string $nameInLayout,
        ?string $template = null,
        array $data = []
    ) {
        parent::__construct($deployedVersion, $layout, $nameInLayout, $template, $data);
        $this->config = $config;
    }

    /**
     * Get support email address.
     * @return string
     */
    public function getSupportEmailAddress(): string
    {
        return (string) $this->config->get(self::SUPPORT_EMAIL_CONFIG);
    }
}

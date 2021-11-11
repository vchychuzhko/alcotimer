<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Http\Request;
use Awesome\Frontend\Model\DeployedVersion;

class Header extends \Awesome\Frontend\Block\Template
{
    private const LOGO_CONFIG_PATH = 'web/logo';

    /**
     * @var Config $config
     */
    private $config;

    /**
     * Header constructor.
     * @param Config $config
     * @param DeployedVersion $deployedVersion
     * @param array $data
     */
    public function __construct(Config $config, DeployedVersion $deployedVersion, array $data = [])
    {
        parent::__construct($deployedVersion, $data);
        $this->config = $config;
    }

    /**
     * Get app logo file path.
     * @return string
     */
    public function getLogo(): string
    {
        return $this->getMediaFileUrl($this->config->get(self::LOGO_CONFIG_PATH));
    }

    /**
     * Check if current page is a Homepage.
     * @return bool
     */
    public function isHomepage(): bool
    {
        $handles = [];

        if ($layout = $this->getLayout()) {
            $handles = $layout->getHandles();
        }

        return in_array(Request::ROOT_ACTION_NAME, $handles, true);
    }
}

<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Http\Request;
use Awesome\Frontend\Model\DeployedVersion;

class Header extends \Awesome\Frontend\Block\Template
{
    private const LOGO_CONFIG = 'web/logo';

    private Config $config;

    private Request $request;

    /**
     * Header constructor.
     * @param Config $config
     * @param DeployedVersion $deployedVersion
     * @param Request $request
     * @param array $data
     */
    public function __construct(
        Config $config,
        DeployedVersion $deployedVersion,
        Request $request,
        array $data = []
    ) {
        parent::__construct($deployedVersion, $data);
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Get app logo url path.
     * @return string
     */
    public function getLogoUrl(): string
    {
        return $this->getMediaFileUrl((string) $this->config->get(self::LOGO_CONFIG));
    }

    /**
     * Check if current page is a Homepage.
     * @return bool
     */
    public function isHomepage(): bool
    {
        return $this->request->getPath() === '/';
    }
}

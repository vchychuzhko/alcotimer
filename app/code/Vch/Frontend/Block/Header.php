<?php
declare(strict_types=1);

namespace Vch\Frontend\Block;

use Vch\Framework\Model\Config;
use Vch\Framework\Model\Http\Request;
use Vch\Frontend\Model\DeployedVersion;
use Vch\Frontend\Model\Layout;

class Header extends \Vch\Frontend\Block\Template
{
    private const LOGO_CONFIG = 'web/logo';

    private Config $config;

    private Request $request;

    /**
     * Header constructor.
     * @param Config $config
     * @param DeployedVersion $deployedVersion
     * @param Layout $layout
     * @param Request $request
     * @param string $nameInLayout
     * @param string|null $template
     * @param array $data
     */
    public function __construct(
        Config $config,
        DeployedVersion $deployedVersion,
        Layout $layout,
        Request $request,
        string $nameInLayout,
        ?string $template = null,
        array $data = []
    ) {
        parent::__construct($deployedVersion, $layout, $nameInLayout, $template, $data);
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

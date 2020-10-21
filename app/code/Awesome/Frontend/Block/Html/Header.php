<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block\Html;

use Awesome\Framework\Model\Http;

class Header extends \Awesome\Frontend\Block\Template
{
    private const LOGO_CONFIG_PATH = 'web/logo';

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
        return in_array(Http::ROOT_ACTION_NAME, $this->renderer->getHandles(), true);
    }
}

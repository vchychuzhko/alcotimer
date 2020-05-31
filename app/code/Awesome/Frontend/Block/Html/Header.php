<?php

namespace Awesome\Frontend\Block\Html;

use Awesome\Framework\Model\Http;

class Header extends \Awesome\Frontend\Block\Template
{
    private const LOGO_PATH_CONFIG = 'web/logo_path';

    /**
     * Get app logo file path.
     * @return string
     */
    public function getLogo()
    {
        return $this->getMediaFileUrl($this->config->get(self::LOGO_PATH_CONFIG));
    }

    /**
     * Check if current page is a Homepage.
     * @return bool
     */
    public function isHomepage()
    {
        return in_array(Http::ROOT_ACTION_NAME, $this->renderer->getHandles());
    }
}

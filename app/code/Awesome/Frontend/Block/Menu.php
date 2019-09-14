<?php

namespace Awesome\Frontend\Block;

class Menu extends \Awesome\Base\Block\Template
{
    private const SUPPORT_EMAIL_CONFIG = 'support_email_address';
    //@TODO: move this to future Contact module

    /**
     * Get support email address.
     * @return string
     */
    public function getSupportEmailAddress()
    {
        return $this->config->getConfig(self::SUPPORT_EMAIL_CONFIG);
    }
}

<?php

namespace Awesome\Ui\Block;

class Menu extends \Awesome\Framework\Block\Template
{
    private const SUPPORT_EMAIL_CONFIG = 'support_email_address';
    //@TODO: move this to future Contact module

    /**
     * Get support email address.
     * @return string
     */
    public function getSupportEmailAddress()
    {
        return $this->config->get(self::SUPPORT_EMAIL_CONFIG);
    }
}

<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

class Menu extends \Awesome\Frontend\Block\Template
{
    private const SUPPORT_EMAIL_CONFIG = 'support_email_address';
    //@TODO: move this to future Contact module

    /**
     * Get support email address.
     * @return string
     */
    public function getSupportEmailAddress(): string
    {
        return $this->config->get(self::SUPPORT_EMAIL_CONFIG);
    }
}

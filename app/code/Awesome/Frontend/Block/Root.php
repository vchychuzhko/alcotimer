<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

class Root extends \Awesome\Frontend\Block\Template
{
    /**
     * @inheritDoc
     */
    protected $template = 'Awesome_Frontend::root.phtml';

    /**
     * Get current page locale.
     * @return string
     */
    public function getLocale(): string
    {
        // @TODO: Implement locale resolving along with translation
        return 'en';
    }
}

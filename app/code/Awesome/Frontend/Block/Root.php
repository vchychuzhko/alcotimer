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
     * Get body class by page handle.
     * @return string
     */
    public function getBodyClass(): string
    {
        return str_replace(['-', '_'], ['', '-'], implode(' ', $this->renderer->getHandles()));
    }
}

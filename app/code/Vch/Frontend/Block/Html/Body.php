<?php
declare(strict_types=1);

namespace Vch\Frontend\Block\Html;

class Body extends \Vch\Frontend\Block\Container
{
    /**
     * Get body node name.
     * @return string
     */
    public function getHtmlTag(): string
    {
        return 'body';
    }

    /**
     * Get body class by page handles.
     * @return string
     */
    public function getHtmlClass(): string
    {
        return str_replace(['-', '_'], ['', '-'], $this->layout->getHandle());
    }
}

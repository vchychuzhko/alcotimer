<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block\Error;

class NotFound extends \Awesome\Frontend\Block\Template implements \Awesome\Frontend\Block\ErrorBlockInterface
{
    /**
     * @inheritDoc
     */
    public function getErrorTitle()
    {
        return '404: ' . __('Page Not Found');
    }

    /**
     * @inheritDoc
     */
    public function getErrorDescription()
    {
        return __('Seems, page you are looking for is not present.');
    }
}
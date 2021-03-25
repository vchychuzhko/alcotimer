<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block\Error;

class Forbidden extends \Awesome\Frontend\Block\Template implements \Awesome\Frontend\Block\ErrorBlockInterface
{
    /**
     * @inheritDoc
     */
    public function getErrorTitle()
    {
        return '403: ' . __('Forbidden');
    }

    /**
     * @inheritDoc
     */
    public function getErrorDescription()
    {
        return __('The page or file you are trying to access is closed for viewing.');
    }
}
<?php

namespace Awesome\Frontend\Block\Error;

class Forbidden extends \Awesome\Framework\Block\Template implements \Awesome\Frontend\Block\ErrorBlockInterface
{
    /**
     * @inheritDoc
     */
    public function getErrorTitle()
    {
        return '403 error: Forbidden';
    }

    /**
     * @inheritDoc
     */
    public function getErrorDescription()
    {
        return 'The page or file you are trying to access is closed for viewing.';
    }
}
<?php

namespace Awesome\Frontend\Block\Error;

class NotFound extends \Awesome\Framework\Block\Template implements \Awesome\Frontend\Block\ErrorBlockInterface
{
    /**
     * @inheritDoc
     */
    public function getErrorTitle()
    {
        return '404 error: Not Found';
    }

    /**
     * @inheritDoc
     */
    public function getErrorDescription()
    {
        return 'Seems, page you are looking for is not present.';
    }
}
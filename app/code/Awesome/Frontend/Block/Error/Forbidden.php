<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block\Error;

class Forbidden extends \Awesome\Frontend\Block\Template implements \Awesome\Frontend\Block\ErrorInterface
{
    /**
     * @inheritDoc
     */
    public function getErrorTitle(): string
    {
        return '403 error: Forbidden';
    }

    /**
     * @inheritDoc
     */
    public function getErrorDescription(): string
    {
        return 'The page or file you are trying to access is closed for viewing.';
    }
}
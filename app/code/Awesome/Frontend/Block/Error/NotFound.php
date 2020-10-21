<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block\Error;

class NotFound extends \Awesome\Frontend\Block\Template implements \Awesome\Frontend\Block\ErrorInterface
{
    /**
     * @inheritDoc
     */
    public function getErrorTitle(): string
    {
        return '404 error: Not Found';
    }

    /**
     * @inheritDoc
     */
    public function getErrorDescription(): string
    {
        return 'Seems, page you are looking for is not present.';
    }
}
<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

interface ErrorInterface
{
    /**
     * Get error page title.
     * @return string
     */
    public function getErrorTitle(): string;

    /**
     * Get error page content.
     * @return string
     */
    public function getErrorDescription(): string;
}
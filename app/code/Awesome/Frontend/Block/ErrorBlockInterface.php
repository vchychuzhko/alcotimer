<?php

namespace Awesome\Frontend\Block;

interface ErrorBlockInterface
{
    /**
     * Get error page title.
     * @return string
     */
    public function getErrorTitle();

    /**
     * Get error page content.
     * @return string
     */
    public function getErrorDescription();
}
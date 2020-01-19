<?php

namespace Awesome\Frontend\Block;

abstract class AbstractErrorBlock extends \Awesome\Framework\Block\Template
{
    /**
     * Get error page title.
     * @return string
     */
    abstract public function getErrorTitle();

    /**
     * Get error page content.
     * @return string
     */
    abstract public function getErrorDescription();
}